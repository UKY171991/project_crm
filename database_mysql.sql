-- MySQL dump from SQLite conversion
-- Generated on 2026-02-11 11:05:19

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `migrations` ("id" integer primary key autoincrement not null, "migration" varchar not null, "batch" integer not null);

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2025_02_01_100000_create_crm_tables', '2'),
('6', '2026_02_01_085417_create_settings_table', '3'),
('7', '2026_02_01_090120_add_budget_to_projects_table', '4'),
('8', '2026_02_01_090500_add_currency_to_payments_table', '5'),
('9', '2026_02_01_090734_add_currency_to_payments_table', '5'),
('10', '2026_02_01_090955_add_currency_to_projects_table', '6'),
('11', '2026_02_01_091000_add_currency_to_projects_table', '6'),
('12', '2026_02_01_091922_add_payment_date_to_payments_table', '7'),
('13', '2026_02_01_092336_create_currencies_table', '8'),
('14', '2026_02_01_094512_add_remarks_to_projects_table', '9'),
('15', '2026_02_01_095847_create_project_remarks_table', '10'),
('16', '2026_02_02_021912_create_project_status_changes_table', '11'),
('17', '2026_02_02_022253_create_notifications_table', '12'),
('18', '2026_02_03_081938_create_expenses_table', '13'),
('19', '2026_02_03_092444_add_city_to_clients_table', '14'),
('20', '2026_02_03_103410_add_is_active_to_users_table', '15'),
('21', '2026_02_03_111547_add_urls_to_projects_table', '16'),
('22', '2026_02_03_112704_create_attendances_table', '17'),
('23', '2026_02_03_114027_rename_total_minutes_to_total_seconds_in_attendances_table', '18'),
('24', '2026_02_03_114320_add_idle_seconds_to_attendances_table', '19'),
('25', '2026_02_03_114333_create_hr_system_tables', '19'),
('26', '2026_02_03_114359_create_screenshots_table', '19'),
('27', '2026_02_03_123924_add_timezone_to_users_table', '20'),
('28', '2026_02_04_110837_add_status_to_expenses_table', '21');

CREATE TABLE `password_reset_tokens` ("email" varchar not null, "token" varchar not null, "created_at" datetime, primary key ("email"));

CREATE TABLE `sessions` ("id" varchar not null, "user_id" integer, "ip_address" varchar, "user_agent" text, "payload" text not null, "last_activity" integer not null, primary key ("id"));

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('NpX4PTF9A3ilCRUhdfNZ6z9Nf6CvNvAIPbedE8ua', '1', '152.58.130.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoickdjcnRodUhaRjRwUlFpSlNYTGVPTFNBMWtsVGtiYXdscDhzYUxhUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vY3JtLmRldmxvcGVyLnNwYWNlL2F0dGVuZGFuY2UiO3M6NToicm91dGUiO3M6MTY6ImF0dGVuZGFuY2UuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', '1770801819');

CREATE TABLE `cache` ("key" varchar not null, "value" text not null, "expiration" integer not null, primary key ("key"));

CREATE TABLE `cache_locks` ("key" varchar not null, "owner" varchar not null, "expiration" integer not null, primary key ("key"));

CREATE TABLE `jobs` ("id" integer primary key autoincrement not null, "queue" varchar not null, "payload" text not null, "attempts" integer not null, "reserved_at" integer, "available_at" integer not null, "created_at" integer not null);

CREATE TABLE `job_batches` ("id" varchar not null, "name" varchar not null, "total_jobs" integer not null, "pending_jobs" integer not null, "failed_jobs" integer not null, "failed_job_ids" text not null, "options" text, "cancelled_at" integer, "created_at" integer not null, "finished_at" integer, primary key ("id"));

CREATE TABLE `failed_jobs` ("id" integer primary key autoincrement not null, "uuid" varchar not null, "connection" text not null, "queue" text not null, "payload" text not null, "exception" text not null, "failed_at" datetime not null default CURRENT_TIMESTAMP);

CREATE TABLE `roles` ("id" integer primary key autoincrement not null, "name" varchar not null, "slug" varchar not null, "created_at" datetime, "updated_at" datetime);

INSERT INTO `roles` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
('1', 'Master', 'master', '2026-02-01 07:52:36', '2026-02-01 07:52:36'),
('2', 'Admin', 'admin', '2026-02-01 07:52:36', '2026-02-01 07:52:36'),
('3', 'Client', 'client', '2026-02-01 07:52:36', '2026-02-01 07:52:36'),
('4', 'User', 'user', '2026-02-01 07:52:36', '2026-02-01 07:52:36');

CREATE TABLE `users` ("id" integer primary key autoincrement not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "remember_token" varchar, "created_at" datetime, "updated_at" datetime, "role_id" integer, "created_by" integer, "deleted_at" datetime, "is_active" tinyint(1) not null default '1', "timezone" varchar not null default 'UTC', foreign key("role_id") references "roles"("id") on delete set null, foreign key("created_by") references "users"("id"));

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `created_by`, `deleted_at`, `is_active`, `timezone`) VALUES
('1', 'Master User', 'umakant171991@gmail.com', NULL, '$2y$12$YgW/lq8Lw9c0KHUoMiokheIl4hcvUIZmGLToW/zA5AzHyEkWp5sDi', NULL, '2026-02-01 07:52:37', '2026-02-01 07:58:16', '1', NULL, NULL, '1', 'UTC'),
('2', 'Admin Manager', 'manager@firm.com', '2026-02-01 07:52:37', '$2y$12$e.JTtiWZQQVCJla1JqVLtOXd75c4CYBzeXmq9TgO7Rri2nsCPEGyG', NULL, '2026-02-01 07:52:37', '2026-02-01 07:52:37', '2', '1', NULL, '1', 'UTC'),
('3', 'Startup Founder', 'client@startup.com', '2026-02-01 07:52:37', '$2y$12$c/OPzPfOvW3RL2wtJtJMIekYI1kRhX0k7OwIH0QXKfGrofUnqgrdW', NULL, '2026-02-01 07:52:37', '2026-02-01 07:52:37', '3', '2', NULL, '1', 'UTC'),
('4', 'Developer One', 'dev@firm.com', '2026-02-01 07:52:37', '$2y$12$mry6i5rlwZNjRMiXAK1P6uFtKRHOOw0cBF5vSCkLZgKb1RBscdPJK', NULL, '2026-02-01 07:52:37', '2026-02-03 10:49:06', '4', '2', NULL, '0', 'UTC'),
('5', '200 pages rs 50/pages', 'client1@gmail.com', NULL, '$2y$12$QgpLw7F1xquRYTvbPgprQeLZZae2KUF3gUQoIPpW.zJz0ZCwBZDHq', NULL, '2026-02-03 08:34:48', '2026-02-03 08:34:48', '3', '1', NULL, '1', 'UTC'),
('6', 'Amzad', 'Client2@gmail.com', NULL, '$2y$12$AUJnEq8JPKh/yIFGxERzRePMnhHdMg92V0PwnijPUaOv/fNvvRGg.', NULL, '2026-02-03 08:36:14', '2026-02-03 08:36:14', '3', '1', NULL, '1', 'UTC'),
('7', 'Dharmendra Bachheriya', 'Client3@gmail.com', NULL, '$2y$12$AV87djwh4ygLGpeRtq6TE.4PORjtj9uHuLwtmHoYivrPORqnP.tBK', NULL, '2026-02-03 08:37:25', '2026-02-03 08:37:25', '3', '1', NULL, '1', 'UTC'),
('8', 'Uniform Store Freelancer', 'Client4@gmail.com', NULL, '$2y$12$ih/bOFZzod1Z9iSqLYurD.HDNZhd74ObDxzfWvvhrZVxwGdyGGqs.', NULL, '2026-02-03 08:39:51', '2026-02-03 08:39:51', '3', '1', NULL, '1', 'UTC'),
('9', 'Brendan Australia', 'info@sketchfurniture.com.au', NULL, '$2y$12$MgfwS3WG1a.h1MZu9B3iAOqwzLfHA.KmR7N1GBB/KpK42qzXV.0oe', NULL, '2026-02-03 08:40:50', '2026-02-03 08:40:50', '3', '1', NULL, '1', 'UTC'),
('10', 'Gyas', 'Client5@gmail.com', NULL, '$2y$12$hZOg4Br.KRCEaWHfMtS3neJ1Vha.sUzjCN5B59cXeJcJbqWNcayoC', NULL, '2026-02-03 09:17:53', '2026-02-03 09:17:53', '3', '1', NULL, '1', 'UTC'),
('11', 'Ravi T', 'Client6@gmail.com', NULL, '$2y$12$F1lh4hyyt3NNwGtWPMBwveP0RgWEnEABaJG7Cm2yE1fFyGohKtBzW', NULL, '2026-02-03 09:21:22', '2026-02-03 09:21:22', '3', '1', NULL, '1', 'UTC'),
('12', 'Vishal', 'Client7@gmail.com', NULL, '$2y$12$/bUZQLRUFmYObmO9NDxVaeF7xRGLmqGTMB55h6Qw8icrDp8ut0DGO', NULL, '2026-02-03 09:22:01', '2026-02-03 09:22:01', '3', '1', NULL, '1', 'UTC'),
('13', 'Umakant Yadav', 'uky171991@gmail.com', NULL, '$2y$12$rBhSlQ4XgYQmSJIJqlvEc.XMphuHrJJGVJeyjXI3RY/hKTyysqG7C', NULL, '2026-02-03 09:37:06', '2026-02-03 09:37:06', '4', '1', NULL, '1', 'UTC'),
('14', 'abc', 'abc@gmail.com', NULL, '$2y$12$V9cNp4qrWDEZy2WxixotYeRECVdamgACVFhgvYapFbjJbX2k0v5Gq', NULL, '2026-02-03 14:24:41', '2026-02-03 14:24:41', NULL, NULL, NULL, '1', 'UTC');

CREATE TABLE `clients` ("id" integer primary key autoincrement not null, "user_id" integer not null, "company_name" varchar not null, "phone" varchar, "address" text, "status" varchar check ("status" in ('active', 'inactive')) not null default 'active', "created_at" datetime, "updated_at" datetime, "city" varchar, foreign key("user_id") references "users"("id") on delete cascade);

INSERT INTO `clients` (`id`, `user_id`, `company_name`, `phone`, `address`, `status`, `created_at`, `updated_at`, `city`) VALUES
('1', '3', 'Startup Inc', '1234567890', '123 Tech Park', 'active', '2026-02-01 07:52:37', '2026-02-01 07:52:37', NULL),
('2', '5', '200 pages rs 50/pages', '+919648122946', '', 'active', '2026-02-03 08:34:48', '2026-02-03 08:34:48', NULL),
('3', '6', 'Amzad', '+91 95400 52228', 'Delehi', 'active', '2026-02-03 08:36:14', '2026-02-03 08:36:14', NULL),
('4', '7', 'Dharmendra Bachheriya', '+91 83064 02805', 'Rajsthan', 'active', '2026-02-03 08:37:25', '2026-02-03 08:37:25', NULL),
('5', '8', 'Uniform Store Freelancer', '+91 97668 33555', 'https://62.72.30.186:8090/
admin
iS74q$%@1902K34A

https://urbaneparent.com/
navedjaffrey@gmail.com. pass- Superflex@4321', 'active', '2026-02-03 08:39:51', '2026-02-09 12:03:08', NULL),
('6', '9', 'Brendan Australia', '+61 414 739 495', '+61 414 739 495', 'active', '2026-02-03 08:40:50', '2026-02-03 08:40:50', NULL),
('7', '10', 'Gyas', '97114 47614', 'Lucknow', 'active', '2026-02-03 09:17:53', '2026-02-03 09:17:53', NULL),
('8', '11', 'Ravi T', '+91 9860900484', 'Mumbai', 'active', '2026-02-03 09:21:22', '2026-02-03 09:21:22', NULL),
('9', '12', 'Vishal', '08427722958', 'Punjab', 'active', '2026-02-03 09:22:01', '2026-02-03 09:22:01', NULL);

CREATE TABLE `projects` ("id" integer primary key autoincrement not null, "uuid" varchar not null, "client_id" integer not null, "title" varchar not null, "description" text, "start_date" date, "end_date" date, "status" varchar check ("status" in ('Pending', 'Running', 'Completed', 'Canceled')) not null default 'Pending', "created_by" integer not null, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, "budget" numeric not null default '0', "currency" varchar not null default 'USD', "urls" text, foreign key("client_id") references "clients"("id") on delete cascade, foreign key("created_by") references "users"("id"));

INSERT INTO `projects` (`id`, `uuid`, `client_id`, `title`, `description`, `start_date`, `end_date`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`, `budget`, `currency`, `urls`) VALUES
('1', 'bfa0aa5e-6875-4000-986c-79c5621b9d43', '3', 'https://allcurepharmacys.com/', 'Login button is not working

Not discused about payment', '2026-02-01 00:00:00', '2026-03-14 00:00:00', 'Pending', '3', '2026-02-01 07:52:37', '2026-02-03 11:23:23', NULL, '500', 'INR', '[{\"label\":\"Devloping url\",\"url\":\"https:\\/\\/allcurepharmacys.com\\/\"}]'),
('2', '1dda2779-49d0-4a31-a296-a9ff121e2ba1', '5', 'altitude', 'Initial MVP for the mobile app.
Refrence url https://bhishmsharma3.wixstudio.com/altitude', '2026-02-03 00:00:00', '2026-02-07 00:00:00', 'Running', '1', '2026-02-01 07:52:37', '2026-02-03 11:22:54', NULL, '4000', 'INR', '[{\"label\":\"Devloping url\",\"url\":\"https:\\/\\/altitude.devloper.space\\/\"}]'),
('3', 'cef32d47-2ce8-4d8a-a279-31b3c3c5f4f8', '4', 'https://d2.devloper.space/', '', '', '2026-02-07 00:00:00', 'Completed', '1', '2026-02-03 10:35:59', '2026-02-03 11:55:00', NULL, '3000', 'INR', NULL),
('4', '5d4e6d86-ec29-41ae-9442-f79c15583b21', '2', '200 page website', 'Git name  rice

discused for  Rs 10000 for 200 pges will be same', '2026-02-03 00:00:00', '2026-02-07 00:00:00', 'Canceled', '1', '2026-02-03 10:38:36', '2026-02-04 15:18:56', NULL, '0', 'INR', '[{\"label\":\"devloping url\",\"url\":\"https:\\/\\/200.devloper.space\\/\"}]'),
('5', '668d250c-77d9-4d41-bfba-f37c7b12dbc6', '4', 'https://d1.devloper.space/', '', '2026-02-03 00:00:00', '2026-02-03 00:00:00', 'Completed', '1', '2026-02-03 10:42:55', '2026-02-03 10:43:01', NULL, '3000', 'INR', NULL),
('6', 'a9c4636d-2dcf-42f4-93c6-9a259e14b0ba', '5', 'https://urbaneparent.com/', 'Cpanel
https://62.72.30.186:8090/
admin
iS74q$%@1902K34A', '2026-02-04 00:00:00', '2026-02-04 00:00:00', 'Completed', '1', '2026-02-04 15:14:01', '2026-02-04 15:21:50', NULL, '1000', 'INR', '[{\"label\":\"devlopment url\",\"url\":\"https:\\/\\/urbaneparent.com\\/\"}]'),
('7', 'cdc363dd-6340-4ae8-8058-6683ce5855ae', '7', 'school management', '', '2026-02-09 00:00:00', '2026-02-09 00:00:00', 'Running', '1', '2026-02-05 20:02:13', '2026-02-09 12:08:58', NULL, '0', 'INR', '[{\"label\":\"Devlopment url\",\"url\":\"https:\\/\\/school.thewebbrain.in\\/\"}]'),
('8', '83f16bdc-08a2-460c-854c-917dec1164c1', '4', 'https://d1.devloper.space/service', '', '2026-02-07 00:00:00', '2026-02-14 00:00:00', 'Completed', '1', '2026-02-07 10:43:13', '2026-02-08 05:58:26', NULL, '500', 'INR', '[{\"label\":\"Devloping url\",\"url\":\"https:\\/\\/d1.devloper.space\\/service\"}]'),
('9', '6f8c0ff9-82de-4e7f-adae-c1b5fed725e5', '5', 'https://urbaneparent.com/', 'no need to country and  state select  on checkout time  

need to quick payment', '2026-02-09 00:00:00', '2026-02-09 00:00:00', 'Running', '1', '2026-02-09 11:54:32', '2026-02-09 11:59:58', NULL, '0', 'INR', '[]'),
('10', 'a84b133e-fd6e-4c98-af19-ce6dd623a6ed', '9', 'Vishal Website mantinance', '', '2026-02-09 00:00:00', '2026-02-09 00:00:00', 'Completed', '1', '2026-02-09 11:56:58', '2026-02-09 11:59:30', NULL, '1380', 'INR', '[]');

CREATE TABLE `project_assignees` ("id" integer primary key autoincrement not null, "project_id" integer not null, "user_id" integer not null, "assigned_by" integer, "created_at" datetime, "updated_at" datetime, foreign key("project_id") references "projects"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade, foreign key("assigned_by") references "users"("id"));

INSERT INTO `project_assignees` (`id`, `project_id`, `user_id`, `assigned_by`, `created_at`, `updated_at`) VALUES
('4', '1', '13', '1', '2026-02-03 09:39:59', '2026-02-03 09:39:59'),
('5', '2', '13', '1', '2026-02-03 10:32:38', '2026-02-03 10:32:38'),
('6', '3', '13', '1', '2026-02-03 10:36:13', '2026-02-03 10:36:13'),
('7', '5', '13', '1', '2026-02-03 10:49:20', '2026-02-03 10:49:20'),
('8', '4', '13', '1', '2026-02-03 10:52:29', '2026-02-03 10:52:29'),
('9', '8', '13', '1', '2026-02-08 05:58:10', '2026-02-08 05:58:10'),
('10', '7', '13', '1', '2026-02-08 06:56:58', '2026-02-08 06:56:58'),
('11', '9', '13', '1', '2026-02-10 09:51:19', '2026-02-10 09:51:19');

CREATE TABLE `media_files` ("id" integer primary key autoincrement not null, "project_id" integer not null, "file_type" varchar not null, "file_path" varchar not null, "file_name" varchar not null, "size_kb" integer, "mime_type" varchar, "uploaded_by" integer not null, "created_at" datetime, "updated_at" datetime, foreign key("project_id") references "projects"("id") on delete cascade, foreign key("uploaded_by") references "users"("id"));

CREATE TABLE `payments` ("id" integer primary key autoincrement not null, "project_id" integer not null, "amount" numeric not null, "payment_method" varchar, "payment_status" varchar check ("payment_status" in ('Paid', 'Unpaid', 'Partial')) not null default 'Unpaid', "transaction_id" varchar, "created_by" integer not null, "created_at" datetime, "updated_at" datetime, "currency" varchar not null default 'USD', "payment_date" date, foreign key("project_id") references "projects"("id") on delete cascade, foreign key("created_by") references "users"("id"));

INSERT INTO `payments` (`id`, `project_id`, `amount`, `payment_method`, `payment_status`, `transaction_id`, `created_by`, `created_at`, `updated_at`, `currency`, `payment_date`) VALUES
('5', '5', '2500', 'UPI', 'Partial', '', '1', '2026-02-03 10:43:37', '2026-02-03 10:43:37', 'INR', '2026-02-03 00:00:00'),
('6', '6', '1000', 'UPI', 'Paid', '', '1', '2026-02-04 15:15:38', '2026-02-04 15:15:38', 'INR', '2026-02-01 00:00:00'),
('7', '5', '500', 'UPI', 'Paid', '', '1', '2026-02-05 13:26:26', '2026-02-05 13:26:26', 'INR', '2026-02-05 00:00:00'),
('9', '10', '1380', 'UPI', 'Paid', '', '1', '2026-02-09 11:57:14', '2026-02-09 11:57:14', 'INR', '2026-02-09 00:00:00');

CREATE TABLE `settings` ("id" integer primary key autoincrement not null, "key" varchar not null, "value" text, "created_at" datetime, "updated_at" datetime);

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
('1', 'system_title', 'CRM', '2026-02-01 08:56:48', '2026-02-01 09:57:10'),
('2', 'system_logo', 'settings/123mFEpeFNH4srdOfWXalcP1bsyTcA7VWEZFbFCQ.jpg', '2026-02-01 08:56:48', '2026-02-01 08:58:42'),
('3', 'system_favicon', 'settings/dCavQWiB56C46jtkl7ZnpTPfAX9014VimKCHLO14.png', '2026-02-01 08:56:48', '2026-02-01 08:58:42'),
('4', 'registration_enabled', '0', '2026-02-05 11:30:10', '2026-02-08 06:51:26'),
('5', 'cron_email', 'uky171991@gmail.com', '2026-02-08 06:47:49', '2026-02-08 06:47:49'),
('6', 'cron_key', 'crm_tasks_cron_2026', '2026-02-08 06:47:49', '2026-02-08 06:47:49'),
('7', 'mail_mailer', 'smtp', '2026-02-08 06:47:49', '2026-02-08 06:51:26'),
('8', 'mail_host', 'mail.devloper.space', '2026-02-08 06:47:49', '2026-02-08 06:51:26'),
('9', 'mail_port', '587', '2026-02-08 06:47:49', '2026-02-08 06:47:49'),
('10', 'mail_username', 'info@devloper.space', '2026-02-08 06:47:49', '2026-02-08 06:51:26'),
('11', 'mail_password', 'Uma@171991', '2026-02-08 06:47:49', '2026-02-08 06:47:49'),
('12', 'mail_encryption', 'tls', '2026-02-08 06:47:49', '2026-02-08 06:47:49'),
('13', 'mail_from_address', 'info@devloper.space', '2026-02-08 06:47:49', '2026-02-08 06:47:49'),
('14', 'mail_from_name', 'CRM', '2026-02-08 06:47:49', '2026-02-08 06:47:49');

CREATE TABLE `currencies` ("id" integer primary key autoincrement not null, "code" varchar not null, "name" varchar, "symbol" varchar, "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime);

INSERT INTO `currencies` (`id`, `code`, `name`, `symbol`, `is_active`, `created_at`, `updated_at`) VALUES
('1', 'USD', 'US Dollar', '$', '1', '2026-02-01 09:23:58', '2026-02-01 09:23:58'),
('2', 'INR', 'Indian Rupee', '₹', '1', '2026-02-01 09:23:58', '2026-02-01 09:23:58'),
('3', 'EUR', 'Euro', '€', '1', '2026-02-01 09:23:58', '2026-02-01 09:23:58'),
('4', 'GBP', 'British Pound', '£', '1', '2026-02-01 09:23:58', '2026-02-01 09:23:58');

CREATE TABLE `project_remarks` ("id" integer primary key autoincrement not null, "project_id" integer not null, "user_id" integer not null, "remark" text not null, "created_at" datetime, "updated_at" datetime, foreign key("project_id") references "projects"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade);

INSERT INTO `project_remarks` (`id`, `project_id`, `user_id`, `remark`, `created_at`, `updated_at`) VALUES
('1', '1', '4', 'Started project', '2026-02-02 02:13:37', '2026-02-02 02:13:37'),
('2', '2', '1', 'Not dised about payment', '2026-02-03 10:32:17', '2026-02-03 10:32:17'),
('3', '4', '1', 'Project canceled client want in wordpress but we approch in laravel', '2026-02-04 11:06:36', '2026-02-04 11:06:36'),
('4', '6', '1', 'cpanel

https://62.72.30.186:8090/
admin
iS74q$%@1902K34A', '2026-02-04 15:14:01', '2026-02-04 15:14:01'),
('5', '8', '1', 'create slider on college details  page ', '2026-02-09 05:32:02', '2026-02-09 05:32:02'),
('6', '7', '1', '1-otp integration  2-email integration  3-shipping (ship rocket) integration 4-School based filtering - when search 5- Bindle offer (shirt+pant+tie) 6- one page checkout - parents can order uniforms in under a minute', '2026-02-09 12:08:17', '2026-02-09 12:08:17'),
('7', '9', '1', 'Loading issue done', '2026-02-10 09:50:53', '2026-02-10 09:50:53');

CREATE TABLE `project_status_changes` ("id" integer primary key autoincrement not null, "project_id" integer not null, "user_id" integer not null, "old_status" varchar not null, "new_status" varchar not null, "status" varchar not null default 'pending', "processed_by" integer, "processed_at" datetime, "created_at" datetime, "updated_at" datetime, foreign key("project_id") references "projects"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade, foreign key("processed_by") references "users"("id"));

INSERT INTO `project_status_changes` (`id`, `project_id`, `user_id`, `old_status`, `new_status`, `status`, `processed_by`, `processed_at`, `created_at`, `updated_at`) VALUES
('1', '1', '4', 'Running', 'Pending', 'approved', '1', '2026-02-02 02:25:42', '2026-02-02 02:21:06', '2026-02-02 02:25:42');

CREATE TABLE `notifications` ("id" varchar not null, "type" varchar not null, "notifiable_type" varchar not null, "notifiable_id" integer not null, "data" text not null, "read_at" datetime, "created_at" datetime, "updated_at" datetime, primary key ("id"));

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('2d23b652-1698-4975-ac98-97f550c5803b', 'App\\Notifications\\ProjectStatusChangedNotification', 'App\\Models\\User', '4', '{\"project_id\":1,\"title\":\"E-Commerce Redesign\",\"message\":\"Your status change request for project E-Commerce Redesign was APPROVED.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/projects\\/1\"}', NULL, '2026-02-02 02:25:41', '2026-02-02 02:25:41'),
('59571449-2c14-4e16-8036-9fcde4f86fc5', 'App\\Notifications\\ProjectStatusChangedNotification', 'App\\Models\\User', '4', '{\"project_id\":1,\"title\":\"E-Commerce Redesign\",\"message\":\"Your status change request for project E-Commerce Redesign was APPROVED.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/projects\\/1\"}', NULL, '2026-02-02 02:25:43', '2026-02-02 02:25:43');

CREATE TABLE `expenses` ("id" integer primary key autoincrement not null, "uuid" varchar not null, "description" varchar not null, "amount" numeric not null, "currency" varchar not null default 'USD', "expense_date" date not null, "category" varchar, "project_id" integer, "user_id" integer not null, "deleted_at" datetime, "created_at" datetime, "updated_at" datetime, "status" varchar not null default 'Paid', foreign key("project_id") references "projects"("id") on delete set null, foreign key("user_id") references "users"("id"));

INSERT INTO `expenses` (`id`, `uuid`, `description`, `amount`, `currency`, `expense_date`, `category`, `project_id`, `user_id`, `deleted_at`, `created_at`, `updated_at`, `status`) VALUES
('1', '925c36b3-a975-44ee-8dc6-948723d43270', 'Testing', '400', 'INR', '2026-02-03 00:00:00', 'Home Accessory', NULL, '1', NULL, '2026-02-03 08:31:08', '2026-02-03 08:31:08', 'Paid'),
('2', '39ab2587-64b9-43bd-98da-bbd5cad5d467', 'Provident modi minu', '3', 'INR', '1978-06-08 00:00:00', 'Ullam quam aut dolor', '4', '13', '2026-02-03 12:19:54', '2026-02-03 12:19:39', '2026-02-03 12:19:54', 'Paid'),
('3', 'fa415127-e533-44e1-817c-f7b177d8f3e5', 'Saurabh', '100', 'INR', '2026-02-04 00:00:00', 'Home Accessory', NULL, '1', NULL, '2026-02-04 11:33:36', '2026-02-09 11:52:00', 'Paid'),
('4', 'f8041ad3-f6bc-4671-b9ff-7766466d6fef', 'Akhilesh Mishra', '100', 'INR', '2026-02-04 00:00:00', 'Home Accessory', NULL, '1', NULL, '2026-02-04 11:33:53', '2026-02-04 11:43:47', 'Pending'),
('5', 'af2f6a15-1c75-40f2-bc25-73876f51c639', 'Bnarsi Yadav Newada', '180', 'INR', '2026-02-04 00:00:00', 'Home Accessory', NULL, '1', NULL, '2026-02-04 11:34:24', '2026-02-04 11:43:53', 'Pending'),
('6', '344bf592-a0e0-4696-a220-577aeb34dd1a', 'Sani Yadav', '2020', 'INR', '2026-02-04 00:00:00', 'Home Accessory', NULL, '1', NULL, '2026-02-04 11:34:40', '2026-02-09 11:52:49', 'Pending'),
('7', '27b4d59a-4f98-4e1c-b00e-ac267303cf2d', 'Saksham & Sarthak', '35', 'INR', '2026-02-04 00:00:00', 'Home Accessory', NULL, '1', NULL, '2026-02-04 11:47:24', '2026-02-04 15:16:29', 'Paid'),
('8', '92eef131-7ead-44bc-a79a-5974b267a760', 'Sani Yadav', '1000', 'INR', '2026-02-09 00:00:00', 'Home Accessory', NULL, '1', NULL, '2026-02-09 11:52:41', '2026-02-09 11:52:41', 'Paid'),
('9', 'e8d100a7-bce3-4fc3-b851-71cde7d9fffb', 'Sagar', '550', 'INR', '2026-02-09 00:00:00', 'Home Accessory', NULL, '1', NULL, '2026-02-09 12:21:34', '2026-02-09 12:21:34', 'Pending'),
('10', '33db59b4-3b1b-47db-89f4-828a511e668c', 'Saksham sarthak', '36', 'INR', '2026-02-10 00:00:00', 'Home Accessory', NULL, '1', NULL, '2026-02-10 09:46:50', '2026-02-10 14:23:04', 'Paid'),
('11', 'badd8574-03bb-43e3-91e7-27757fb6fb66', 'Saksham sarthak', '10', 'INR', '2026-02-11 00:00:00', '', NULL, '1', NULL, '2026-02-11 09:26:11', '2026-02-11 09:26:11', 'Paid'),
('12', 'd1377d92-db65-484e-b7e7-e9ea21a9a5ad', 'Chini samosa biskit', '135', 'INR', '2026-02-10 00:00:00', '', NULL, '1', NULL, '2026-02-11 09:27:10', '2026-02-11 09:27:10', 'Paid');

CREATE TABLE `attendances` ("id" integer primary key autoincrement not null, "user_id" integer not null, "date" date not null, "clock_in" datetime, "clock_out" datetime, "total_seconds" integer not null default '0', "created_at" datetime, "updated_at" datetime, "idle_seconds" integer not null default '0', foreign key("user_id") references "users"("id") on delete cascade);

INSERT INTO `attendances` (`id`, `user_id`, `date`, `clock_in`, `clock_out`, `total_seconds`, `created_at`, `updated_at`, `idle_seconds`) VALUES
('1', '1', '2026-02-03 00:00:00', '2026-02-03 11:32:03', '2026-02-03 11:32:06', '0.052118066666667', '2026-02-03 11:32:03', '2026-02-03 11:32:06', '0'),
('2', '1', '2026-02-03 00:00:00', '2026-02-03 11:32:27', '2026-02-03 11:41:46', '559.704381', '2026-02-03 11:32:27', '2026-02-03 11:41:46', '0'),
('3', '1', '2026-02-03 00:00:00', '2026-02-03 11:41:48', '2026-02-03 11:43:11', '83.127073', '2026-02-03 11:41:48', '2026-02-03 11:43:11', '0'),
('4', '1', '2026-02-03 00:00:00', '2026-02-03 11:43:12', '2026-02-03 11:47:14', '242.1904', '2026-02-03 11:43:12', '2026-02-03 11:47:14', '0'),
('5', '1', '2026-02-03 00:00:00', '2026-02-03 11:47:16', '2026-02-03 15:53:24', '14781.925684', '2026-02-03 11:47:16', '2026-02-03 15:53:37', '3150'),
('6', '13', '2026-02-03 00:00:00', '2026-02-03 12:16:37', NULL, '743.859253', '2026-02-03 12:16:37', '2026-02-03 12:29:00', '30'),
('7', '14', '2026-02-03 00:00:00', '2026-02-03 14:24:41', NULL, '33.001814', '2026-02-03 14:24:41', '2026-02-03 14:25:14', '0'),
('8', '1', '2026-02-03 00:00:00', '2026-02-03 16:03:55', '2026-02-03 16:09:14', '449.441343', '2026-02-03 16:03:55', '2026-02-03 16:11:24', '130'),
('9', '1', '2026-02-03 00:00:00', '2026-02-03 16:09:30', '2026-02-03 16:13:45', '872.317521', '2026-02-03 16:09:30', '2026-02-03 16:24:02', '140'),
('10', '1', '2026-02-03 00:00:00', '2026-02-03 16:34:44', '2026-02-03 16:38:30', '236.448212', '2026-02-03 16:34:44', '2026-02-03 16:38:40', '10'),
('11', '1', '2026-02-03 00:00:00', '2026-02-03 16:38:40', '2026-02-03 16:39:08', '34.846284', '2026-02-03 16:38:40', '2026-02-03 16:39:14', '0'),
('12', '1', '2026-02-03 00:00:00', '2026-02-03 16:39:13', '2026-02-03 16:42:13', '193.20316', '2026-02-03 16:39:13', '2026-02-03 16:42:26', '0'),
('13', '1', '2026-02-03 00:00:00', '2026-02-03 16:42:25', '2026-02-03 16:48:31', '20260.002599', '2026-02-03 16:42:25', '2026-02-03 22:20:05', '60'),
('14', '1', '2026-02-03 00:00:00', '2026-02-03 22:20:50', '2026-02-03 22:22:37', '114.51505', '2026-02-03 22:20:50', '2026-02-03 22:22:44', '20'),
('15', '1', '2026-02-03 00:00:00', '2026-02-03 22:23:19', '2026-02-03 22:25:15', '125.832973', '2026-02-03 22:23:19', '2026-02-03 22:25:24', '10'),
('16', '1', '2026-02-03 00:00:00', '2026-02-03 22:25:24', '2026-02-03 22:30:52', '345.584962', '2026-02-03 22:25:24', '2026-02-03 22:31:09', '30'),
('17', '1', '2026-02-03 00:00:00', '2026-02-03 22:31:09', '2026-02-03 22:32:28', '182.904129', '2026-02-03 22:31:09', '2026-02-03 22:34:11', '20'),
('18', '1', '2026-02-03 00:00:00', '2026-02-03 22:34:04', '2026-02-03 22:40:58', '419.281821', '2026-02-03 22:34:04', '2026-02-03 22:41:03', '60'),
('19', '1', '2026-02-03 00:00:00', '2026-02-03 22:41:02', '2026-02-03 22:44:13', '196.674779', '2026-02-03 22:41:02', '2026-02-03 22:44:18', '0'),
('20', '1', '2026-02-03 00:00:00', '2026-02-03 22:44:32', '2026-02-03 22:45:19', '50.445941', '2026-02-03 22:44:32', '2026-02-03 22:45:22', '0'),
('21', '1', '2026-02-04 00:00:00', '2026-02-04 10:50:38', '2026-02-04 11:57:39', '4028.232659', '2026-02-04 10:50:38', '2026-02-04 11:57:46', '480'),
('22', '1', '2026-02-04 00:00:00', '2026-02-04 13:33:29', '2026-02-04 13:33:39', '10.683263', '2026-02-04 13:33:29', '2026-02-04 13:33:39', '0'),
('23', '1', '2026-02-04 00:00:00', '2026-02-04 15:02:19', '2026-02-04 15:09:27', '428.759283', '2026-02-04 15:02:19', '2026-02-04 15:09:27', '0'),
('24', '1', '2026-02-04 00:00:00', '2026-02-04 17:51:54', '2026-02-04 18:12:06', '1212.220247', '2026-02-04 17:51:54', '2026-02-04 18:12:06', '0'),
('25', '1', '2026-02-05 00:00:00', '2026-02-05 13:26:49', '2026-02-05 13:31:37', '299.131813', '2026-02-05 13:26:49', '2026-02-05 13:31:48', '80'),
('26', '1', '2026-02-07 00:00:00', '2026-02-07 10:42:17', '2026-02-07 12:30:53', '6516.967098', '2026-02-07 10:42:17', '2026-02-07 12:30:53', '300'),
('27', '1', '2026-02-07 00:00:00', '2026-02-07 14:50:12', '2026-02-07 18:29:14', '13142.852275', '2026-02-07 14:50:12', '2026-02-07 18:29:14', '0'),
('28', '1', '2026-02-07 00:00:00', '2026-02-07 18:29:17', '2026-02-07 18:29:24', '7.324759', '2026-02-07 18:29:17', '2026-02-07 18:29:24', '0'),
('29', '1', '2026-02-09 00:00:00', '2026-02-09 12:17:11', '2026-02-09 13:20:45', '3825.197794', '2026-02-09 12:17:11', '2026-02-09 13:20:56', '610'),
('30', '1', '2026-02-09 00:00:00', '2026-02-09 13:22:57', '2026-02-09 15:23:40', '7243.235495', '2026-02-09 13:22:57', '2026-02-09 15:23:40', '0'),
('31', '1', '2026-02-10 00:00:00', '2026-02-10 15:38:46', '2026-02-10 15:46:01', '472.060466', '2026-02-10 15:38:46', '2026-02-10 15:46:38', '80'),
('32', '1', '2026-02-10 00:00:00', '2026-02-10 15:46:04', '2026-02-10 15:54:12', '3551.289237', '2026-02-10 15:46:04', '2026-02-10 16:45:15', '590'),
('33', '1', '2026-02-10 00:00:00', '2026-02-10 16:49:29', '2026-02-10 17:05:14', '950.028018', '2026-02-10 16:49:29', '2026-02-10 17:05:19', '190'),
('34', '1', '2026-02-11 00:00:00', '2026-02-11 10:13:12', '2026-02-11 14:49:03', '16565.676627', '2026-02-11 10:13:12', '2026-02-11 14:49:17', '870'),
('35', '1', '2026-02-11 00:00:00', '2026-02-11 14:49:17', '2026-02-11 14:49:26', '20.358373', '2026-02-11 14:49:17', '2026-02-11 14:49:37', '0'),
('36', '1', '2026-02-11 00:00:00', '2026-02-11 14:55:26', '2026-02-11 14:55:36', '10.619243', '2026-02-11 14:55:26', '2026-02-11 14:55:36', '0');

CREATE TABLE `user_salaries` ("id" integer primary key autoincrement not null, "user_id" integer not null, "base_salary" numeric not null, "currency" varchar not null default 'INR', "working_days_per_month" integer not null default '22', "daily_working_hours" integer not null default '8', "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade);

INSERT INTO `user_salaries` (`id`, `user_id`, `base_salary`, `currency`, `working_days_per_month`, `daily_working_hours`, `created_at`, `updated_at`) VALUES
('1', '1', '30000', 'INR', '26', '8', '2026-02-03 11:59:47', '2026-02-03 12:34:53');

CREATE TABLE `leaves` ("id" integer primary key autoincrement not null, "user_id" integer not null, "date" date not null, "type" varchar check ("type" in ('Full Day', 'Half Day')) not null default 'Full Day', "reason" varchar, "status" varchar check ("status" in ('Pending', 'Approved', 'Rejected')) not null default 'Approved', "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade);

CREATE TABLE `holidays` ("id" integer primary key autoincrement not null, "date" date not null, "name" varchar not null, "type" varchar check ("type" in ('Festival', 'Regular', 'Other')) not null default 'Festival', "created_at" datetime, "updated_at" datetime);

CREATE TABLE `screenshots` ("id" integer primary key autoincrement not null, "user_id" integer not null, "attendance_id" integer not null, "path" varchar not null, "captured_at" datetime not null, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade, foreign key("attendance_id") references "attendances"("id") on delete cascade);

SET FOREIGN_KEY_CHECKS=1;
