-- DESTRUCTIVE, MANUAL-ONLY production reset. Do not add to migrations/deployment.
-- 1. Take a database backup/export first.
-- 2. Pause the welcome-email cron, queue workers, and application writes.
-- 3. Run this ENTIRE file in Supabase SQL Editor, not a selected fragment.
-- Keeps franckmercurio25@gmail.com AND every superadmin account.
-- Keeps Management tables, including facilities, maintenance_staff, categories,
-- event_departments, event_request_types, event_intended_users, approval chains.
-- Deleting other users may clear Management owner/creator references (SET NULL).
-- Stored uploads are NOT deleted. Sequence IDs are NOT reset or reused.
-- Any SQL error rolls back the transaction. Run ROLLBACK after an error before retrying.

-- One statement: local variables survive throughout the reset, even when the
-- SQL client executes separate statements in separate transactions/sessions.
do $campfix_reset$
declare
    keep_user_ids bigint[];
    target_oids oid[];
    target_names text[];
    table_name text;
    unexpected text;
    targets text;
    remaining bigint;
begin
    perform set_config('lock_timeout', '10s', true);
    lock table public.users in access exclusive mode;

    select array_agg(id) into keep_user_ids from public.users
    where lower(trim(email)) = 'franckmercurio25@gmail.com'
       or is_superadmin = true
       or role = 'superadmin';

    if (select count(*) from public.users
        where lower(trim(email)) = 'franckmercurio25@gmail.com'
          and role = 'mis'
          and coalesce(is_deleted, false) = false
          and coalesce(is_archived, false) = false) <> 1 then
        raise exception 'Reset stopped: expected exactly one active MIS account franckmercurio25@gmail.com.';
    end if;
-- Explicit allowlist: never TRUNCATE CASCADE. An unexpected dependency causes
-- an error rather than silently clearing a Management/configuration table.
select array_agg(name order by name),
       array_agg(to_regclass(format('public.%I', name))::oid order by name)
into target_names, target_oids
from unnest(array[
    'welcome_email_deliveries',
    'notifications', 'activity_logs', 'superadmin_activity_logs',
    'audit_reports', 'audit_concerns', 'report_status_logs',
    'event_discussions', 'concern_reporters',
    'user_archived_event_requests', 'user_archived_facility_requests',
    'user_archived_concerns', 'user_archived_reports',
    'reports', 'concerns', 'event_requests', 'facility_requests',
    'password_reset_tokens', 'otp_verifications', 'personal_access_tokens',
    'sessions', 'user_rate_limits', 'jobs', 'job_batches', 'failed_jobs'
]) as tables(name)
where to_regclass(format('public.%I', name)) is not null;

    -- Protect tables outside the allowlist from cascading user deletion.
    select string_agg(c.conrelid::regclass::text || ' (' || c.conname || ')', ', ')
    into unexpected
    from pg_constraint c
    where c.contype = 'f'
      and c.confrelid = 'public.users'::regclass
      and c.confdeltype = 'c'
      and c.conrelid <> 'public.users'::regclass
      and not (c.conrelid = any(target_oids));
    if unexpected is not null then
        raise exception 'Reset stopped: review unexpected user cascade dependencies: %', unexpected;
    end if;

    -- A self-referencing cascade could otherwise delete a preserved login.
    if exists (select 1 from pg_constraint
        where contype = 'f' and conrelid = 'public.users'::regclass
          and confrelid = 'public.users'::regclass and confdeltype = 'c') then
        raise exception 'Reset stopped: users has a self-referencing cascade; review before reset.';
    end if;

    select string_agg(format('public.%I', name), ', ' order by name)
    into targets from unnest(target_names) as t(name);
    if targets is null then
        raise exception 'Reset stopped: no operational tables found.';
    end if;
    execute 'TRUNCATE TABLE ' || targets || ' CONTINUE IDENTITY RESTRICT';

delete from public.users u
where not (u.id = any(keep_user_ids));

-- Leave system folders/settings in place, but remove their obsolete counts.
    if to_regclass('public.user_archive_folders') is not null then
        update public.user_archive_folders set user_count = 0;
    end if;
    if to_regclass('public.archive_folders') is not null then
        update public.archive_folders set item_count = 0;
    end if;

-- Verify before committing; unexpected trigger side effects abort the reset.
    if (select count(*) from public.users) <> cardinality(keep_user_ids)
       or exists (select 1 from unnest(keep_user_ids) as k(id)
                  where not exists (select 1 from public.users u where u.id = k.id)) then
        raise exception 'Reset stopped: preserved-account verification failed.';
    end if;
    foreach table_name in array target_names loop
        execute format('select count(*) from public.%I', table_name) into remaining;
        if remaining <> 0 then
            raise exception 'Reset stopped: % still has % records.', table_name, remaining;
        end if;
    end loop;
end $campfix_reset$;

select id, name, email, role, is_superadmin
from public.users order by id;
