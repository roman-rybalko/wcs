create table anacron (start_time integer not null, period_time integer not null, user_key integer, data varbinary(1024), id integer primary key auto_increment not null);
create index anacron_idx1 on anacron(user_key);
create index anacron_idx2 on anacron(start_time);
