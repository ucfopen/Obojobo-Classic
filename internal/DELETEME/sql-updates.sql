# !!!!!!!!!!!!! RUN BUILDLOs.php BEFORE UPDATING THE PHP CLASSES !!!!!!!!!!!!!!!!!!!!!!!!!

# Update the php classes from svn

ALTER TABLE  `lo_map_authors` ADD UNIQUE (
`userID` ,
`loID`
);

ALTER TABLE  `lo_media` DROP  `scorable`