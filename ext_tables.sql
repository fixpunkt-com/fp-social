# Extend table structure for table 'sys_file_storage'
CREATE TABLE sys_file_storage (
    protected tinyint(3) DEFAULT 0 NOT NULL,
    protected_by_default int(11) unsigned DEFAULT 0 NOT NULL
);

#
# Table structure for table 'tx_fpfileprotector_domain_model_protection'
#
CREATE TABLE tx_fpfileprotector_domain_model_protection (
    storage int(11) unsigned DEFAULT 0 NOT NULL,
    folder varchar(255) DEFAULT '' NOT NULL,
    fe_login tinyint(3) DEFAULT 0 NOT NULL,
    be_login tinyint(3) DEFAULT 0 NOT NULL,
    user_groups int(11) unsigned DEFAULT 0 NOT NULL,
    users int(11) unsigned DEFAULT 0 NOT NULL,
);