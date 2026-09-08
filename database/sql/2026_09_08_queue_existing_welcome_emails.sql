-- Deploy the updated welcome-email sender BEFORE running this script.
-- Run the 2026_09_05_welcome_email_queue.sql setup first if necessary.
-- Queues all active users, regardless of role. Does not change passwords.
-- Existing delivery records (including sent/uncertain/failed) are never reset.
-- Emails sent before the delivery ledger existed cannot be inferred here:
-- reconcile those users against Brevo's logs before running this backfill.
begin;

insert into public.welcome_email_deliveries
    (user_id, status, attempts, created_at, updated_at)
select id, 'pending', 0, now(), now()
from public.users
where coalesce(is_deleted, false) = false
  and coalesce(is_archived, false) = false
  and nullif(trim(email), '') is not null
on conflict (user_id) do nothing;

commit;

select status, count(*) as users
from public.welcome_email_deliveries
group by status
order by status;
