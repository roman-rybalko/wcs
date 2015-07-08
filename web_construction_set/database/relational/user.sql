create table users (login varchar, passhash varchar, id integer primary key autoincrement);
create unique index users_idx on users (login);
