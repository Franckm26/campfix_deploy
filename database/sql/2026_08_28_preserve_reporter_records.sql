-- Run once in Supabase SQL Editor if production does not execute Laravel migrations.
-- This preserves concerns/reports after permanent account deletion.

update public.concerns records
set reporter_name = coalesce(records.reporter_name, (select log.old_values->>'name' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'name' is not null order by log.created_at asc limit 1), users.name, 'Unknown'),
    reporter_email = coalesce(records.reporter_email, (select log.old_values->>'email' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'email' is not null order by log.created_at asc limit 1), users.email),
    reporter_role = coalesce(records.reporter_role, (select log.old_values->>'role' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'role' is not null order by log.created_at asc limit 1), users.role),
    reporter_department = coalesce(records.reporter_department, (select log.old_values->>'department' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'department' is not null order by log.created_at asc limit 1), users.department),
    reporter_phone = coalesce(records.reporter_phone, (select log.old_values->>'phone' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'phone' is not null order by log.created_at asc limit 1), users.phone),
    reporter_student_id = coalesce(records.reporter_student_id, (select log.old_values->>'student_id' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'student_id' is not null order by log.created_at asc limit 1), users.student_id)
from public.users users
where users.id = records.user_id;

update public.reports records
set reported_by_name = coalesce(records.reported_by_name, (select log.old_values->>'name' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'name' is not null order by log.created_at asc limit 1), users.name, 'Unknown'),
    reporter_email = coalesce(records.reporter_email, (select log.old_values->>'email' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'email' is not null order by log.created_at asc limit 1), users.email),
    reporter_role = coalesce(records.reporter_role, (select log.old_values->>'role' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'role' is not null order by log.created_at asc limit 1), users.role),
    reporter_department = coalesce(records.reporter_department, (select log.old_values->>'department' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'department' is not null order by log.created_at asc limit 1), users.department),
    reporter_phone = coalesce(records.reporter_phone, (select log.old_values->>'phone' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'phone' is not null order by log.created_at asc limit 1), users.phone),
    reporter_student_id = coalesce(records.reporter_student_id, (select log.old_values->>'student_id' from public.activity_logs log where log.item_user_id = records.user_id and log.action = 'user_updated' and log.created_at >= records.created_at and log.old_values->>'student_id' is not null order by log.created_at asc limit 1), users.student_id)
from public.users users
where users.id = records.user_id;

alter table public.concerns alter column user_id drop not null;
alter table public.reports alter column user_id drop not null;

do $$
declare item record;
begin
    for item in
        select tc.table_name, tc.constraint_name
        from information_schema.table_constraints tc
        join information_schema.key_column_usage kcu
          on tc.constraint_name = kcu.constraint_name and tc.table_schema = kcu.table_schema
        where tc.table_schema = 'public'
          and tc.table_name in ('concerns', 'reports')
          and tc.constraint_type = 'FOREIGN KEY'
          and kcu.column_name = 'user_id'
    loop
        execute format('alter table public.%I drop constraint %I', item.table_name, item.constraint_name);
    end loop;
end $$;

alter table public.concerns
    add constraint concerns_user_id_foreign foreign key (user_id) references public.users(id) on delete set null;
alter table public.reports
    add constraint reports_user_id_foreign foreign key (user_id) references public.users(id) on delete set null;

-- Enforce immutable submission-time reporter information even for direct SQL updates.
create or replace function public.preserve_concern_reporter_snapshot()
returns trigger as $$
begin
    if old.reporter_name is not null then new.reporter_name := old.reporter_name; end if;
    if old.reporter_email is not null then new.reporter_email := old.reporter_email; end if;
    if old.reporter_role is not null then new.reporter_role := old.reporter_role; end if;
    if old.reporter_department is not null then new.reporter_department := old.reporter_department; end if;
    if old.reporter_phone is not null then new.reporter_phone := old.reporter_phone; end if;
    if old.reporter_student_id is not null then new.reporter_student_id := old.reporter_student_id; end if;
    return new;
end;
$$ language plpgsql;

drop trigger if exists concerns_preserve_reporter_snapshot on public.concerns;
create trigger concerns_preserve_reporter_snapshot
before update on public.concerns
for each row execute function public.preserve_concern_reporter_snapshot();

create or replace function public.preserve_report_reporter_snapshot()
returns trigger as $$
begin
    if old.reported_by_name is not null then new.reported_by_name := old.reported_by_name; end if;
    if old.reporter_email is not null then new.reporter_email := old.reporter_email; end if;
    if old.reporter_role is not null then new.reporter_role := old.reporter_role; end if;
    if old.reporter_department is not null then new.reporter_department := old.reporter_department; end if;
    if old.reporter_phone is not null then new.reporter_phone := old.reporter_phone; end if;
    if old.reporter_student_id is not null then new.reporter_student_id := old.reporter_student_id; end if;
    return new;
end;
$$ language plpgsql;

drop trigger if exists reports_preserve_reporter_snapshot on public.reports;
create trigger reports_preserve_reporter_snapshot
before update on public.reports
for each row execute function public.preserve_report_reporter_snapshot();
