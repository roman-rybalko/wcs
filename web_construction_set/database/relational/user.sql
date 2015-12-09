create table users (login varchar(256) not null, passhash varchar(256) not null, id integer primary key auto_increment not null, time integer not null);
create unique index users_idx on users (login(32));
