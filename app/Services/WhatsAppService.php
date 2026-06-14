<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private $type;
    private $customGatewayUrl;

    public function __construct(array $config = [])
    {
        $this->accessToken = $config['access_token'] ?? config('services.whatsapp.access_token');
        $this->phoneNumberId = $config['phone_number_id'] ?? config('services.whatsapp.phone_number_id');
        $this->version = $config['version'] ?? config('services.whatsapp.version', 'v18.0');
        $this->type = $config['type'] ?? config('services.whatsapp.type', 'official');
        $this->customGatewayUrl = $config['custom_gateway_url'] ?? config('services.whatsapp.custom_gateway_url');
        
        if ($this->type === 'fast2sms') {
            $this->baseUrl = "https://www.fast2sms.com/dev/whatsapp/{$this->version}";
        } else {
            $this->baseUrl = "https://graph.facebook.com/{$this->version}";
        }
    }

    /**
     * Get headers for WhatsApp API request based on gateway type
     */
    private function getHeaders()
    {
        if ($this->type === 'fast2sms') {
            return [
                'authorization' => $this->accessToken,
                'Content-Type' => 'application/json'
            ];
        }

        return [
            'Authorization' => "Bearer {$this->accessToken}",
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Send project status update message to client
     */
    public function sendProjectStatusUpdate($client, $project, $oldStatus, $newStatus)
    {
        try {
            if (!$client->phone || !$this->accessToken || !$this->phoneNumberId) {
                Log::warning('WhatsApp service not configured or client phone missing', [
                    'client_id' => $client->id,
                    'project_id' => $project->id
                ]);
                return false;
            }

            $templateName = $this->getTemplateName($oldStatus, $newStatus);
            
            if ($this->type === 'fast2sms' && is_numeric($templateName)) {
                $variables = implode('|', [
                    $project->title,
                    $project->client->company_name ?? 'N/A',
                    $oldStatus,
                    $newStatus,
                    now()->format('Y-m-d'),
                    config('app.name', 'CRM System')
                ]);
                return $this->sendFast2SMSSimple($client->phone, $templateName, $variables);
            }
            
            $response = Http::withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatPhoneNumber($client->phone),
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => config('services.whatsapp.language', 'en')
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $project->title
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $project->client->company_name ?? 'N/A'
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $oldStatus
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $newStatus
                                ],
                                [
                                    'type' => 'text',
                                    'text' => now()->format('Y-m-d')
                                ],
                                [
                                    'type' => 'text',
                                    'text' => config('app.name', 'CRM System')
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'client_id' => $client->id,
                    'project_id' => $project->id,
                    'template' => $templateName,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Failed to send WhatsApp message', [
                    'client_id' => $client->id,
                    'project_id' => $project->id,
                    'template' => $templateName,
                    'response' => $response->json()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp service error', [
                'client_id' => $client->id,
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send project payment status message to client
     */
    public function sendPaymentStatusUpdate($client, $project, $payment)
    {
        try {
            if (!$client->phone || !$this->accessToken || !$this->phoneNumberId) {
                Log::warning('WhatsApp service not configured or client phone missing', [
                    'client_id' => $client->id,
                    'project_id' => $project->id,
                    'payment_id' => $payment->id ?? null
                ]);
                return false;
            }

            $templateName = config('services.whatsapp.payment_template_name', 'project_payment_status');
            
            $project->load('payments'); // Ensure payments are loaded
            $totalPaid = $project->total_paid;
            $balance = $project->balance;

            if ($this->type === 'fast2sms' && is_numeric($templateName)) {
                if ($templateName === '23166') {
                    $variables = "{$payment->currency} " . number_format($payment->amount, 2);
                } else {
                    $variables = implode('|', [
                        $project->title,
                        $project->client->company_name ?? 'N/A',
                        "{$payment->currency} " . number_format($payment->amount, 2),
                        "{$project->currency} " . number_format($totalPaid, 2),
                        "{$project->currency} " . number_format($balance, 2),
                        config('app.name', 'CRM System')
                    ]);
                }
                return $this->sendFast2SMSSimple($client->phone, $templateName, $variables);
            }

            $response = Http::withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatPhoneNumber($client->phone),
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => config('services.whatsapp.language', 'en')
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $project->title
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $project->client->company_name ?? 'N/A'
                                ],
                                [
                                    'type' => 'text',
                                    'text' => "{$payment->currency} " . number_format($payment->amount, 2)
                                ],
                                [
                                    'type' => 'text',
                                    'text' => "{$project->currency} " . number_format($totalPaid, 2)
                                ],
                                [
                                    'type' => 'text',
                                    'text' => "{$project->currency} " . number_format($balance, 2)
                                ],
                                [
                                    'type' => 'text',
                                    'text' => config('app.name', 'CRM System')
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp payment status sent successfully', [
                    'client_id' => $client->id,
                    'project_id' => $project->id,
                    'payment_id' => $payment->id ?? null,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Failed to send WhatsApp payment status', [
                    'client_id' => $client->id,
                    'project_id' => $project->id,
                    'payment_id' => $payment->id ?? null,
                    'response' => $response->json()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp service error for payment', [
                'client_id' => $client->id,
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get template name based on status change
     */
    private function getTemplateName($oldStatus, $newStatus)
    {
        $defaultTemplate = config('services.whatsapp.template_name', 'project_status_update');

        // If the default template is numeric (like Fast2SMS template ID 5231) or if configured to use default only, use it for all status changes
        if (is_numeric($defaultTemplate) || config('services.whatsapp.use_default_only', true)) {
            return $defaultTemplate;
        }

        $templateMap = [
            'pending_to_running' => 'project_status_pending_to_running',
            'running_to_pending_payment' => 'project_status_running_to_pending_payment',
            'pending_payment_to_completed' => 'project_status_pending_payment_to_completed',
            'pending_to_canceled' => 'project_status_pending_to_canceled',
            'running_to_canceled' => 'project_status_running_to_canceled',
            'pending_payment_to_canceled' => 'project_status_pending_payment_to_canceled',
            'canceled_to_pending' => 'project_status_canceled_to_pending',
            'canceled_to_running' => 'project_status_canceled_to_running',
        ];
        
        $key = strtolower($oldStatus) . '_to_' . strtolower($newStatus);
        
        // Return specific template if found, otherwise default template
        return $templateMap[$key] ?? $defaultTemplate;
    }

    /**
     * Send custom text message
     */
    public function sendTextMessage($phoneNumber, $message)
    {
        try {
            if ($this->type === 'custom' && $this->customGatewayUrl) {
                return $this->sendViaCustomGateway($phoneNumber, $message);
            }

            if (!$phoneNumber || !$this->accessToken || !$this->phoneNumberId) {
                return false;
            }

            $response = Http::withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatPhoneNumber($phoneNumber),
                'type' => 'text',
                'text' => [
                    'body' => $message
                ]
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp text message error', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send message via custom unofficial gateway
     */
    private function sendViaCustomGateway($phoneNumber, $message)
    {
        try {
            $url = $this->customGatewayUrl;
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            
            // Replace placeholders in URL
            $url = str_replace(
                ['{phone}', '{message}', '{text}'],
                [$formattedPhone, urlencode($message), urlencode($message)],
                $url
            );

            Log::info('Sending WhatsApp via custom gateway', ['url' => $url]);
            
            $response = Http::get($url);
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Custom WhatsApp gateway error', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send image message
     */
    public function sendImageMessage($phoneNumber, $imageUrl, $caption = '')
    {
        try {
            if (!$phoneNumber || !$this->accessToken || !$this->phoneNumberId) {
                return false;
            }

            $response = Http::withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatPhoneNumber($phoneNumber),
                'type' => 'image',
                'image' => [
                    'link' => $imageUrl,
                    'caption' => $caption
                ]
            ]);

            if ($response->successful()) {
                return true;
            } else {
                Log::error('WhatsApp image message error', [
                    'phone' => $phoneNumber,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp image message exception', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send WhatsApp proposal template message
     */
    public function sendProposalMessage($phoneNumber, $clientName)
    {
        try {
            if (!$phoneNumber || !$this->accessToken || !$this->phoneNumberId) {
                Log::warning('WhatsApp parameters missing for proposal send');
                return false;
            }

            $templateName = config('services.whatsapp.proposal_template_name', '23169');
            $imageUrl = url('assets/images/dev-plan.png');

            if ($this->type === 'fast2sms' && is_numeric($templateName)) {
                return $this->sendFast2SMSSimple($phoneNumber, $templateName, $clientName, $imageUrl);
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $this->formatPhoneNumber($phoneNumber),
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => config('services.whatsapp.language', 'en')
                    ],
                    'components' => [
                        [
                            'type' => 'header',
                            'parameters' => [
                                [
                                    'type' => 'image',
                                    'image' => [
                                        'link' => $imageUrl
                                    ]
                                ]
                            ]
                        ],
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $clientName
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            Log::debug('Sending WhatsApp proposal request', [
                'url' => "{$this->baseUrl}/{$this->phoneNumberId}/messages",
                'headers' => $this->getHeaders(),
                'payload' => $payload
            ]);

            $response = Http::withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                Log::info('WhatsApp proposal sent successfully', [
                    'phone' => $phoneNumber,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Failed to send WhatsApp proposal', [
                    'phone' => $phoneNumber,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp service error for proposal', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send message using Fast2SMS Simple WhatsApp API
     */
    private function sendFast2SMSSimple($phoneNumber, $messageId, $variables = '', $mediaUrl = null)
    {
        try {
            $params = [
                'authorization' => $this->accessToken,
                'message_id' => $messageId,
                'phone_number_id' => $this->phoneNumberId,
                'numbers' => $this->formatPhoneNumber($phoneNumber),
            ];

            if (!empty($variables)) {
                $params['variables_values'] = $variables;
            }

            if (!empty($mediaUrl)) {
                $params['media_url'] = $mediaUrl;
            }

            Log::debug('Sending Fast2SMS Simple template request', [
                'url' => 'https://www.fast2sms.com/dev/whatsapp',
                'params' => $params
            ]);

            $response = Http::get('https://www.fast2sms.com/dev/whatsapp', $params);

            if ($response->successful()) {
                Log::info('Fast2SMS Simple template sent successfully', [
                    'phone' => $phoneNumber,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Failed to send Fast2SMS Simple template', [
                    'phone' => $phoneNumber,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Fast2SMS Simple template exception', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Format phone number for WhatsApp API
     */
    private function formatPhoneNumber($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if missing (assuming default country code from config)
        if (strlen($phone) === 10) {
            $countryCode = config('services.whatsapp.default_country_code', '91');
            $phone = $countryCode . $phone;
        }
        
        return $phone;
    }

    /**
     * Format project status message
     */
    private function formatProjectStatusMessage($project, $oldStatus, $newStatus)
    {
        return "Project Status Update\n\n" .
               "Project: {$project->title}\n" .
               "Client: {$project->client->company_name}\n" .
               "Status changed from: {$oldStatus}\n" .
               "To: {$newStatus}\n\n" .
               "Updated on: " . now()->format('Y-m-d H:i') . "\n" .
               "Thank you for your business!";
    }

    /**
     * Test WhatsApp connection
     */
    public function testConnection()
    {
        try {
            if ($this->type === 'fast2sms') {
                $response = Http::get("https://www.fast2sms.com/dev/dlt_manager/whatsapp", [
                    'authorization' => $this->accessToken,
                    'type' => 'number'
                ]);
                return $response->successful();
            }

            $headers = $this->getHeaders();
            unset($headers['Content-Type']);
            
            $response = Http::withHeaders($headers)->get("{$this->baseUrl}/me");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
