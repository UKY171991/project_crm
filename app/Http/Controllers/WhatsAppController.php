<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;

class WhatsAppController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Verify webhook for WhatsApp
     */
    public function verifyWebhook(Request $request)
    {
        $verifyToken = config('services.whatsapp.webhook_verify_token');
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp webhook verified successfully');
            return response($challenge, 200);
        }

        Log::warning('WhatsApp webhook verification failed', [
            'mode' => $mode,
            'token' => $token,
            'expected' => $verifyToken
        ]);

        return response('Verification failed', 403);
    }

    /**
     * Handle incoming WhatsApp messages
     */
    public function handleWebhook(Request $request)
    {
        try {
            $data = $request->json()->all();
            
            Log::info('WhatsApp webhook received', ['data' => $data]);

            // Process incoming messages
            if (isset($data['entry'])) {
                foreach ($data['entry'] as $entry) {
                    if (isset($entry['changes'])) {
                        foreach ($entry['changes'] as $change) {
                            if (isset($change['value']['messages'])) {
                                foreach ($change['value']['messages'] as $message) {
                                    $this->processIncomingMessage($message);
                                }
                            }
                        }
                    }
                }
            }

            return response('Webhook processed', 200);
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook processing error', [
                'error' => $e->getMessage(),
                'request_data' => $request->json()->all()
            ]);
            
            return response('Webhook processing failed', 500);
        }
    }

    /**
     * Process incoming message
     */
    private function processIncomingMessage($message)
    {
        try {
            $phoneNumber = $message['from'] ?? null;
            $messageType = $message['type'] ?? null;
            $messageBody = $message['text']['body'] ?? null;

            if (!$phoneNumber || !$messageType) {
                return;
            }

            Log::info('Processing WhatsApp message', [
                'phone' => $phoneNumber,
                'type' => $messageType,
                'body' => $messageBody
            ]);

            // Handle different message types
            switch ($messageType) {
                case 'text':
                    $this->handleTextMessage($phoneNumber, $messageBody);
                    break;
                
                case 'interactive':
                    $this->handleInteractiveMessage($phoneNumber, $message);
                    break;
                
                default:
                    Log::info('Unhandled message type', ['type' => $messageType]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing incoming message', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    /**
     * Handle text messages
     */
    private function handleTextMessage($phoneNumber, $messageBody)
    {
        // Add your custom logic here
        // For example, auto-responses, keyword detection, etc.
        
        if (strtolower($messageBody) === 'status') {
            $this->whatsappService->sendTextMessage(
                $phoneNumber,
                "Please provide your project ID to check status."
            );
        }
    }

    /**
     * Handle interactive messages
     */
    private function handleInteractiveMessage($phoneNumber, $message)
    {
        // Handle button responses, list selections, etc.
        $interactiveType = $message['interactive']['type'] ?? null;
        
        if ($interactiveType === 'button_reply') {
            $buttonId = $message['interactive']['button_reply']['id'] ?? null;
            $this->handleButtonReply($phoneNumber, $buttonId);
        }
    }

    /**
     * Handle button replies
     */
    private function handleButtonReply($phoneNumber, $buttonId)
    {
        // Add logic for button responses
        switch ($buttonId) {
            case 'project_status':
                // Handle project status request
                break;
            case 'contact_support':
                $this->whatsappService->sendTextMessage(
                    $phoneNumber,
                    "Our support team will contact you shortly."
                );
                break;
        }
    }

    /**
     * Test WhatsApp connection
     */
    public function testConnection()
    {
        try {
            $isConnected = $this->whatsappService->testConnection();
            
            return response()->json([
                'status' => $isConnected ? 'success' : 'error',
                'message' => $isConnected ? 'WhatsApp API connection successful' : 'WhatsApp API connection failed'
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp connection test error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test message
     */
    public function sendTestMessage(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|string',
                'message' => 'required|string'
            ]);

            $success = $this->whatsappService->sendTextMessage(
                $request->phone,
                $request->message
            );

            return response()->json([
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Test message sent successfully' : 'Failed to send test message'
            ]);
        } catch (\Exception $e) {
            Log::error('Test message error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Test message failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
