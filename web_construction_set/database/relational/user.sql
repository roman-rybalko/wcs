create table users (login varchar(32) not null, passhash varchar(255) not null, id integer primary key auto_increment not null);
create unique index users_idx on users (login);
