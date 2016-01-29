create table users (login varbinary(256) not null, passhash varbinary(256) not null, id integer primary key auto_increment not null, time integer not null);
create unique index users_idx on users (login);
