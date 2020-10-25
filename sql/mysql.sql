-- Table structure for table lang
-- 

CREATE TABLE lang (
    langid       MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    langcode     VARCHAR(20)           NOT NULL DEFAULT '',
    langdirname  VARCHAR(50)           NOT NULL DEFAULT '',
    langisactive TINYINT(1) UNSIGNED   NOT NULL DEFAULT '1',
    sitename     TEXT                  NOT NULL,
    slogan       TEXT                  NOT NULL,
    footer       TEXT                  NOT NULL,
    charset      VARCHAR(50)           NOT NULL DEFAULT '',
    langcss      VARCHAR(50)           NULL,
    PRIMARY KEY (langid),
    KEY langactive_index (langisactive),
    KEY dirname_index (langdirname)
)
    ENGINE = ISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `lang_blocks`
-- 

CREATE TABLE lang_blocks (
    bid       MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    mid       SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
    langid    MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    langcode  VARCHAR(20)           NOT NULL DEFAULT '',
    blockname VARCHAR(150)          NOT NULL DEFAULT '',
    PRIMARY KEY (bid, langid),
    KEY langid_index (langid, bid),
    KEY mid_index (mid, langid)
)
    ENGINE = ISAM;



-- --------------------------------------------------------

-- 
-- Table structure for table `lang_modules`
-- 

CREATE TABLE lang_modules (
    mid     SMALLINT(5) UNSIGNED  NOT NULL DEFAULT '0',
    langid  MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    modname VARCHAR(150)          NOT NULL DEFAULT '',
    PRIMARY KEY (mid, langid),
    KEY langid_index (langid, mid)
)
    ENGINE = ISAM;


-- --------------------------------------------------------

-- 
-- Table structure for table `lang_user`
-- 

CREATE TABLE lang_user (
    userid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    langid MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    KEY langid_index (langid, userid),
    KEY userid_index (userid, langid)
)
    ENGINE = ISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `lang_name`
-- 

CREATE TABLE lang_name (
    guilangcode VARCHAR(20)  NOT NULL DEFAULT '',
    langcode    VARCHAR(20)  NOT NULL DEFAULT '',
    langname    VARCHAR(150) NOT NULL DEFAULT '',
    PRIMARY KEY (guilangcode, langcode),
    KEY langcode_index (langcode, guilangcode)
)
    ENGINE = ISAM;
