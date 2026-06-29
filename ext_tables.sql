#
# Table structure for table 'tx_fpsocial_domain_model_post'
#
CREATE TABLE tx_fpsocial_domain_model_post (
	accounts int(11) DEFAULT '0' NOT NULL,
	id varchar(255) DEFAULT '' NOT NULL,
	url varchar(255) DEFAULT '' NOT NULL,
	updated_time datetime DEFAULT null,
	headline varchar(255) DEFAULT '' NOT NULL,
	message text,
	pictures int(11) unsigned DEFAULT '0' NOT NULL,
	selected_picture int(11) unsigned DEFAULT '0' NOT NULL,
	link varchar(255) DEFAULT '' NOT NULL,
    origin_deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
    hashtags int(11) unsigned DEFAULT '0' NOT NULL,
    mentions int(11) unsigned DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tx_fpsocial_domain_model_account'
#
CREATE TABLE tx_fpsocial_domain_model_account (
    channel varchar(255) DEFAULT '' NOT NULL,
    posts int(11) DEFAULT '0' NOT NULL,

    synchronize tinyint(4) unsigned DEFAULT '1' NOT NULL,
    last_synchronization datetime DEFAULT null,
    last_successful_synchronization datetime DEFAULT null,
    synchronization_error TEXT,
    synchronization_interval VARCHAR(255) DEFAULT '' NOT NULL,

    access int(11) DEFAULT '0' NOT NULL,
    network varchar(255) DEFAULT '' NOT NULL,
    label varchar(255) DEFAULT '' NOT NULL,
    approve tinyint(1) DEFAULT '1' NOT NULL,

    wp_url varchar(255) DEFAULT 'posts' NOT NULL,
    wp_mode varchar(255) DEFAULT 'posts' NOT NULL,
    wp_tag varchar(255) DEFAULT '' NOT NULL,
    wp_author varchar(255) DEFAULT '' NOT NULL,

    tw_mode varchar(255) DEFAULT 'user_timeline' NOT NULL,
    tw_hashtag varchar(255) DEFAULT '' NOT NULL,

    yt_channel_id varchar(255) DEFAULT '' NOT NULL,

    in_mode varchar(255) DEFAULT 'profile' NOT NULL,
    in_hashtag varchar(255) DEFAULT '' NOT NULL,
    in_hashtag_mode varchar(255) DEFAULT 'recent_media' NOT NULL,

    li_mode varchar(255) DEFAULT 'shares' NOT NULL,
);

#
# Table structure for table 'tx_fpsocial_domain_model_access'
#
CREATE TABLE tx_fpsocial_domain_model_access (
    fp_username varchar(255) DEFAULT '' NOT NULL,
    fp_access_token varchar(255) DEFAULT '' NOT NULL,
);

#
# Table structure for table 'tx_fpsocial_domain_model_picture'
#
CREATE TABLE tx_fpsocial_domain_model_picture (
    uri text,
    uri_identifier text,
    post int(11) DEFAULT '0' NOT NULL,
    filereference int(11) DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tx_fpsocial_domain_model_postlink'
#
CREATE TABLE tx_fpsocial_domain_model_postlink (
    post int(11) DEFAULT '0' NOT NULL,
    account int(11) DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tx_fpsocial_domain_model_hashtag'
#
CREATE TABLE tx_fpsocial_domain_model_hashtag (
    hashtag varchar(255) DEFAULT '' NOT NULL,
    posts int(11) DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tx_fpsocial_domain_model_mention'
#
CREATE TABLE tx_fpsocial_domain_model_mention (
    display_name varchar(255) DEFAULT '' NOT NULL,
    system_name varchar(255) DEFAULT '' NOT NULL,
    posts int(11) DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
    tx_fpsocial_accounts text,
    tx_fpsocial_records text,
    tx_fpsocial_hashtags text,
    tx_fpsocial_post text,
    tx_fpsocial_post_crop TINYINT(3) DEFAULT 0 NOT NULL,
    tx_fpsocial_post_characters INT(11) DEFAULT 0 NOT NULL,
    tx_fpsocial_post_compact TINYINT(3) DEFAULT 0 NOT NULL,
    tx_fpsocial_post_picture TINYINT(3) DEFAULT 0 NOT NULL,
    tx_fpsocial_post_picture_crop TINYINT(3) DEFAULT 0 NOT NULL,
    tx_fpsocial_wall_columns INT(11) DEFAULT 0 NOT NULL,
    tx_fpsocial_wall_rows INT(11) DEFAULT 0 NOT NULL,
    tx_fpsocial_wall_loadnewer_enable TINYINT(3) DEFAULT 0 NOT NULL,
    tx_fpsocial_wall_replace TINYINT(3) DEFAULT 0 NOT NULL,
    tx_fpsocial_wall_loadbefore_enable TINYINT(3) DEFAULT 0 NOT NULL,
    tx_fpsocial_wall_loadbefore_label varchar(255) DEFAULT '' NOT NULL,
);