create table paypal_transactions(id integer primary key auto_increment not null, user_key integer, time integer not null, amt double, currencycode varchar(4), token varchar(32));
create unique index paypal_transactions_idx1 on paypal_transactions(token);
create index paypal_transactions_idx2 on paypal_transactions(user_key);
create table paypal_subscriptions(id integer primary key auto_increment not null, user_key integer, time integer not null, billingagreementid varchar(32));
create unique index paypal_subscriptions_idx1 on paypal_subscriptions(billingagreementid);
create index paypal_subscriptions_idx2 on paypal_subscriptions(user_key);
create table paypal_log(id integer primary key auto_increment not null, user_key integer, time integer not null, correlationid varchar(32), token varchar(32), invnum integer, data varchar(16384));
create index paypal_log_idx1 on paypal_log(user_key);
create index paypal_log_idx2 on paypal_log(time);
create index paypal_log_idx3 on paypal_log(token);
create index paypal_log_idx4 on paypal_log(correlationid);
create index paypal_log_idx5 on paypal_log(invnum);
