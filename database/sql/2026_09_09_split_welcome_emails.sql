-- Run before deploying the split welcome-email sender. Safe to rerun.
-- Existing sent deliveries remain sent; this does not requeue anyone.
begin;
alter table public.welcome_email_deliveries
    add column if not exists email_address_sent_at timestamp(0) without time zone,
    add column if not exists password_sent_at timestamp(0) without time zone;
commit;
