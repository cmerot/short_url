-- Table: redirect
CREATE TABLE redirect ( 
    id         INTEGER  PRIMARY KEY AUTOINCREMENT,
    url_id     INTEGER,
    created_at DATETIME 
);


-- Table: user
CREATE TABLE user ( 
    id    INTEGER PRIMARY KEY AUTOINCREMENT,
    email CHAR    UNIQUE 
);


-- Table: url
CREATE TABLE url ( 
    id         INTEGER      PRIMARY KEY AUTOINCREMENT,
    url        CHAR( 500 ),
    user_id    INTEGER      REFERENCES user ( id ),
    created_at DATETIME 
);

