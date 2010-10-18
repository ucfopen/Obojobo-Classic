"INSERT INTO ".cfg_core_User::TABLE."
SET 
	".cfg_core_User::ID." = 1,
	".cfg_core_User::FIRST." = 'New',
	".cfg_core_User::LAST." = 'Media',
	".cfg_core_User::EMAIL." = 'iturgeon@gmail.com',
	".cfg_core_User::LOGIN_TIME." = 1134416800,
	".cfg_core_User::CREATED_TIME." = 1241655843;

INSERT INTO ".cfg_core_AuthModInternal::TABLE."
SET
	".cfg_core_User::ID." = 1,
	".cfg_core_AuthModInternal::USER_NAME." = '~su',
	".cfg_core_AuthModInternal::PASS." = MD5(CONCAT('3aa40b256fc49edae3a8c752530f1dde', MD5('testPassword2'))),
	".cfg_core_AuthModInternal::SALT." = '3aa40b256fc49edae3a8c752530f1dde',
	".cfg_core_AuthModInternal::PW_CHANGE_TIME." = 0;

INSERT INTO ".cfg_obo_Language::TABLE." SET ".cfg_obo_Language::ID." = 1, ".cfg_obo_Language::NAME." = 'English';
INSERT INTO ".cfg_obo_Language::TABLE." SET ".cfg_obo_Language::ID." = 2, ".cfg_obo_Language::NAME." = 'Dutch';
INSERT INTO ".cfg_obo_Language::TABLE." SET ".cfg_obo_Language::ID." = 3, ".cfg_obo_Language::NAME." = 'French';
INSERT INTO ".cfg_obo_Language::TABLE." SET ".cfg_obo_Language::ID." = 4, ".cfg_obo_Language::NAME." = 'German';
INSERT INTO ".cfg_obo_Language::TABLE." SET ".cfg_obo_Language::ID." = 5, ".cfg_obo_Language::NAME." = 'Icelandic';
INSERT INTO ".cfg_obo_Language::TABLE." SET ".cfg_obo_Language::ID." = 6, ".cfg_obo_Language::NAME." = 'Italian';
INSERT INTO ".cfg_obo_Language::TABLE." SET ".cfg_obo_Language::ID." = 7, ".cfg_obo_Language::NAME." = 'Portuguese';
INSERT INTO ".cfg_obo_Language::TABLE." SET ".cfg_obo_Language::ID." = 8, ".cfg_obo_Language::NAME." = 'Spanish';
INSERT INTO ".cfg_obo_Language::TABLE." SET ".cfg_obo_Language::ID." = 9, ".cfg_obo_Language::NAME." = 'Swedish';
INSERT INTO ".cfg_obo_Language::TABLE." SET ".cfg_obo_Language::ID." = 10, ".cfg_obo_Language::NAME." = 'Welsh';

INSERT INTO ".cfg_obo_Layout::TABLE." SET ".cfg_obo_Layout::ID." = 1, ".cfg_obo_Layout::TITLE." = 'Single Text Area', ".cfg_obo_Layout::THUMB." = 26,  ".cfg_obo_Layout::ITEMS." = '1';
INSERT INTO ".cfg_obo_Layout::TABLE." SET ".cfg_obo_Layout::ID." = 2, ".cfg_obo_Layout::TITLE." = 'Picture Left, Text Right',  ".cfg_obo_Layout::THUMB." = 25, ".cfg_obo_Layout::ITEMS." = '2 3';
INSERT INTO ".cfg_obo_Layout::TABLE." SET ".cfg_obo_Layout::ID." = 3, ".cfg_obo_Layout::TITLE." = 'Media With Description',  ".cfg_obo_Layout::THUMB." = 24, ".cfg_obo_Layout::ITEMS." = '4 5';
INSERT INTO ".cfg_obo_Layout::TABLE." SET ".cfg_obo_Layout::ID." = 4, ".cfg_obo_Layout::TITLE." = 'Text Left, Picture Right',  ".cfg_obo_Layout::THUMB." = 27, ".cfg_obo_Layout::ITEMS." = '6 7';
INSERT INTO ".cfg_obo_Layout::TABLE." SET ".cfg_obo_Layout::ID." = 5, ".cfg_obo_Layout::TITLE." = 'Media Top/MediaBottom',  ".cfg_obo_Layout::THUMB." = 23, ".cfg_obo_Layout::ITEMS." = '8 9';
INSERT INTO ".cfg_obo_Layout::TABLE." SET ".cfg_obo_Layout::ID." = 6, ".cfg_obo_Layout::TITLE." = 'Text Left/Text Right',  ".cfg_obo_Layout::THUMB." = 26, ".cfg_obo_Layout::ITEMS." = '3 6';
INSERT INTO ".cfg_obo_Layout::TABLE." SET ".cfg_obo_Layout::ID." = 7, ".cfg_obo_Layout::TITLE." = 'Media Only',  ".cfg_obo_Layout::THUMB." = 26, ".cfg_obo_Layout::ITEMS." = '10';

INSERT INTO ".cfg_obo_Role::TABLE." SET ".cfg_obo_Role::ID." = 8, ".cfg_obo_Role::ROLE." = 'SuperUser';
INSERT INTO ".cfg_obo_Role::TABLE." SET ".cfg_obo_Role::ID." = 9, ".cfg_obo_Role::ROLE." = 'SuperViewer';
INSERT INTO ".cfg_obo_Role::TABLE." SET ".cfg_obo_Role::ID." = 10, ".cfg_obo_Role::ROLE." = 'ContentCreator';
INSERT INTO ".cfg_obo_Role::TABLE." SET ".cfg_obo_Role::ID." = 12, ".cfg_obo_Role::ROLE." = 'Administrator';
INSERT INTO ".cfg_obo_Role::TABLE." SET ".cfg_obo_Role::ID." = 13, ".cfg_obo_Role::ROLE." = 'WikiEditor';
INSERT INTO ".cfg_obo_Role::TABLE." SET ".cfg_obo_Role::ID." = 14, ".cfg_obo_Role::ROLE." = 'LibraryUser';

INSERT INTO ".cfg_obo_Role::MAP_USER." SET ".cfg_core_User::ID." = 1, ".cfg_obo_Role::ID." = 8;
INSERT INTO ".cfg_obo_Role::MAP_USER." SET ".cfg_core_User::ID." = 1, ".cfg_obo_Role::ID." = 10;
INSERT INTO ".cfg_obo_Role::MAP_USER." SET ".cfg_core_User::ID." = 1, ".cfg_obo_Role::ID." = 12;

INSERT INTO ".cfg_obo_AuthMan::TABLE." SET ".cfg_obo_AuthMan::ID." = 1, ".cfg_obo_AuthMan::MOD_CLASS." = 'core_auth_ModInternal', ".cfg_obo_AuthMan::ACTIVE." = '1', ".cfg_obo_AuthMan::ORDER." = 1;
INSERT INTO ".cfg_obo_AuthMan::TABLE." SET ".cfg_obo_AuthMan::ID." = 4, ".cfg_obo_AuthMan::MOD_CLASS." = 'nm_auth_ModUCFAuth', ".cfg_obo_AuthMan::ACTIVE." = '1', ".cfg_obo_AuthMan::ORDER." = 2;

INSERT INTO ".cfg_obo_Temp::TABLE." SET ".cfg_obo_Temp::ID." = 'AuthMod_PeopleSoft_LastNIDUpdate', ".cfg_obo_Temp::VALUE." = '0';
INSERT INTO ".cfg_obo_Temp::TABLE." SET ".cfg_obo_Temp::ID." = 'System_DB_LastCleanIndex', ".cfg_obo_Temp::VALUE." = '1';
INSERT INTO ".cfg_obo_Temp::TABLE." SET ".cfg_obo_Temp::ID." = 'System_DB_LastCleanTime', ".cfg_obo_Temp::VALUE." = '0';

INSERT INTO ".cfg_obo_Layout::ITEM_TABLE." SET ".cfg_obo_Layout::ITEM_ID." = 1, ".cfg_obo_Layout::ITEM_TITLE." = 'TextArea', ".cfg_obo_Layout::ITEM_COMP." = 1, ".cfg_obo_Layout::ITEM_X." = 0, ".cfg_obo_Layout::ITEM_Y." = 0, ".cfg_obo_Layout::ITEM_W." = 600, ".cfg_obo_Layout::ITEM_H." = 400, ".cfg_obo_Layout::ITEM_DATA." = '[Please input some text]';
INSERT INTO ".cfg_obo_Layout::ITEM_TABLE." SET ".cfg_obo_Layout::ITEM_ID." = 2, ".cfg_obo_Layout::ITEM_TITLE." = 'MediaView', ".cfg_obo_Layout::ITEM_COMP." = 2, ".cfg_obo_Layout::ITEM_X." = 0, ".cfg_obo_Layout::ITEM_Y." = 0, ".cfg_obo_Layout::ITEM_W." = 290, ".cfg_obo_Layout::ITEM_H." = 400, ".cfg_obo_Layout::ITEM_DATA." = '';
INSERT INTO ".cfg_obo_Layout::ITEM_TABLE." SET ".cfg_obo_Layout::ITEM_ID." = 3, ".cfg_obo_Layout::ITEM_TITLE." = 'TextArea', ".cfg_obo_Layout::ITEM_COMP." = 1, ".cfg_obo_Layout::ITEM_X." = 310, ".cfg_obo_Layout::ITEM_Y." = 0, ".cfg_obo_Layout::ITEM_W." = 290, ".cfg_obo_Layout::ITEM_H." = 400, ".cfg_obo_Layout::ITEM_DATA." = '[Please input some text]';
INSERT INTO ".cfg_obo_Layout::ITEM_TABLE." SET ".cfg_obo_Layout::ITEM_ID." = 4, ".cfg_obo_Layout::ITEM_TITLE." = 'MediaView', ".cfg_obo_Layout::ITEM_COMP." = 2, ".cfg_obo_Layout::ITEM_X." = 0, ".cfg_obo_Layout::ITEM_Y." = 0, ".cfg_obo_Layout::ITEM_W." = 600, ".cfg_obo_Layout::ITEM_H." = 330, ".cfg_obo_Layout::ITEM_DATA." = '';
INSERT INTO ".cfg_obo_Layout::ITEM_TABLE." SET ".cfg_obo_Layout::ITEM_ID." = 5, ".cfg_obo_Layout::ITEM_TITLE." = 'TextArea', ".cfg_obo_Layout::ITEM_COMP." = 1, ".cfg_obo_Layout::ITEM_X." = 0, ".cfg_obo_Layout::ITEM_Y." = 340, ".cfg_obo_Layout::ITEM_W." = 600, ".cfg_obo_Layout::ITEM_H." = 60, ".cfg_obo_Layout::ITEM_DATA." = '[Please put a transcript or description of the media file here]';
INSERT INTO ".cfg_obo_Layout::ITEM_TABLE." SET ".cfg_obo_Layout::ITEM_ID." = 6, ".cfg_obo_Layout::ITEM_TITLE." = 'TextArea2', ".cfg_obo_Layout::ITEM_COMP." = 1, ".cfg_obo_Layout::ITEM_X." = 0, ".cfg_obo_Layout::ITEM_Y." = 0, ".cfg_obo_Layout::ITEM_W." = 290, ".cfg_obo_Layout::ITEM_H." = 400, ".cfg_obo_Layout::ITEM_DATA." = '[Please input some text]';
INSERT INTO ".cfg_obo_Layout::ITEM_TABLE." SET ".cfg_obo_Layout::ITEM_ID." = 7, ".cfg_obo_Layout::ITEM_TITLE." = 'MediaView', ".cfg_obo_Layout::ITEM_COMP." = 2, ".cfg_obo_Layout::ITEM_X." = 310, ".cfg_obo_Layout::ITEM_Y." = 0, ".cfg_obo_Layout::ITEM_W." = 290, ".cfg_obo_Layout::ITEM_H." = 400, ".cfg_obo_Layout::ITEM_DATA." = '';
INSERT INTO ".cfg_obo_Layout::ITEM_TABLE." SET ".cfg_obo_Layout::ITEM_ID." = 8, ".cfg_obo_Layout::ITEM_TITLE." = 'MediaView1', ".cfg_obo_Layout::ITEM_COMP." = 2, ".cfg_obo_Layout::ITEM_X." = 0, ".cfg_obo_Layout::ITEM_Y." = 0, ".cfg_obo_Layout::ITEM_W." = 600, ".cfg_obo_Layout::ITEM_H." = 315, ".cfg_obo_Layout::ITEM_DATA." = '';
INSERT INTO ".cfg_obo_Layout::ITEM_TABLE." SET ".cfg_obo_Layout::ITEM_ID." = 9, ".cfg_obo_Layout::ITEM_TITLE." = 'MediaView2', ".cfg_obo_Layout::ITEM_COMP." = 2, ".cfg_obo_Layout::ITEM_X." = 0, ".cfg_obo_Layout::ITEM_Y." = 320, ".cfg_obo_Layout::ITEM_W." = 600, ".cfg_obo_Layout::ITEM_H." = 80, ".cfg_obo_Layout::ITEM_DATA." = '';
INSERT INTO ".cfg_obo_Layout::ITEM_TABLE." SET ".cfg_obo_Layout::ITEM_ID." = 10, ".cfg_obo_Layout::ITEM_TITLE." = 'MediaView', ".cfg_obo_Layout::ITEM_COMP." = 2, ".cfg_obo_Layout::ITEM_X." = 0, ".cfg_obo_Layout::ITEM_Y." = 0, ".cfg_obo_Layout::ITEM_W." = 600, ".cfg_obo_Layout::ITEM_H." = 400, ".cfg_obo_Layout::ITEM_DATA." = '';

INSERT INTO ".cfg_obo_Layout::COMP_TABLE." SET ".cfg_obo_Layout::COMP_ID." = 1, ".cfg_obo_Layout::COMP_TITLE." = 'TextArea';
INSERT INTO ".cfg_obo_Layout::COMP_TABLE." SET ".cfg_obo_Layout::COMP_ID." = 2, ".cfg_obo_Layout::COMP_TITLE." = 'MediaView';"