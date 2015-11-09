create table billing_transactions(id integer primary key auto_increment not null, time integer not null, amount_before integer, amount integer, amount_after integer, user_key integer, data varchar(16384));
create index billing_transactions_idx1 on billing_transactions(user_key, time);
create index billing_transactions_idx2 on billing_transactions(time);
create table billing_accounts(user_key integer primary key, last_transaction_id integer, amount integer not null default 0);
