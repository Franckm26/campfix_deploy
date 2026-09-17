-- Run after deploying the System Administrator access changes.
-- Retains the legacy 'superadmin' role value; the UI calls it System Administrator.
-- Does not reset passwords, welcome records, or report snapshots.
do $$
declare account_id bigint;
begin
    if (select count(*) from public.users
        where lower(trim(email)) = 'franckmercurio25@gmail.com') <> 1 then
        raise exception 'Expected exactly one franckmercurio25@gmail.com account. No changes made.';
    end if;

    select id into account_id from public.users
    where lower(trim(email)) = 'franckmercurio25@gmail.com'
      and coalesce(is_deleted, false) = false
      and coalesce(is_archived, false) = false
    for update;
    if account_id is null then
        raise exception 'The account must be active before promotion. No changes made.';
    end if;

    update public.users
    set role = 'superadmin', is_superadmin = true, updated_at = now()
    where id = account_id;
end $$;

select id, name, email, role, is_superadmin from public.users
where lower(trim(email)) = 'franckmercurio25@gmail.com';
