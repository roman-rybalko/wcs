create table users (login varchar(32), passhash varchar(255), id integer primary key auto_increment);
create unique index users_idx on users (login);
