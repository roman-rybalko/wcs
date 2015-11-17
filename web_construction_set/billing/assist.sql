create table assist_transactions(id integer primary key auto_increment not null, user_key integer, time integer not null, order_number varchar(128) not null, amount double, currency varchar(3), recurring integer(1), bill_number varchar(32), url varchar(1024));
create unique index assist_transactions_idx1 on assist_transactions(order_number);
create index assist_transactions_idx2 on assist_transactions(user_key);
create table assist_subscriptions(id integer primary key auto_increment not null, user_key integer, time integer not null, bill_number varchar(32) not null);
create unique index assist_subscriptions_idx1 on assist_subscriptions(bill_number);
create index assist_subscriptions_idx2 on assist_subscriptions(user_key);
create table assist_log(id integer primary key auto_increment not null, user_key integer, time integer not null, order_number varchar(128), bill_number varchar(32), data varchar(16384));
create index assist_log_idx1 on assist_log(user_key);
create index assist_log_idx2 on assist_log(time);
create index assist_log_idx3 on assist_log(order_number(16));
create index assist_log_idx4 on assist_log(bill_number(16));