create table history(time integer not null, name varchar(256) not null, data varchar(1024), user_key integer, id integer primary key auto_increment not null);
create index history_idx1 on history(user_key);
create index history_idx2 on history(time);