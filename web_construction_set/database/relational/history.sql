create table history(time integer not null, name varbinary(256) not null, data varbinary(16384), user_key integer, id integer primary key auto_increment not null);
create index history_idx1 on history(user_key, time);
create index history_idx2 on history(time);
