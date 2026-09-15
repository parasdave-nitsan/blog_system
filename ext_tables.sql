CREATE TABLE tx_blogsystem_domain_model_category (
    name varchar(255) DEFAULT '' NOT NULL,
    description text DEFAULT '' NOT NULL
);

CREATE TABLE tx_blogsystem_domain_model_blog (
    title varchar(255) DEFAULT '' NOT NULL,
    description text DEFAULT '' NOT NULL,
    author varchar(255) DEFAULT '' NOT NULL,
    publish_date int(11) DEFAULT '0' NOT NULL,
    thumbnail int(11) DEFAULT '0' NOT NULL,
    views int(11) DEFAULT '0' NOT NULL,
    category int(11) DEFAULT '0' NOT NULL
);

create table tx_blogsystem_domain_model_comment (
    blog int(11) DEFAULT '0' NOT NULL,
    user varchar(255) DEFAULT '' NOT NULL,
    content text DEFAULT '' NOT NULL,
    publish_date int(11) DEFAULT '0' NOT NULL,
    comment_reply int(11) DEFAULT '0' NOT NULL
);