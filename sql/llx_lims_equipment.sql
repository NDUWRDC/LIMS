-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE llx_lims_equipment(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) DEFAULT '(PROV)' NOT NULL, 
	fk_product integer NOT NULL, 
	category smallint NOT NULL, 
	maintenance smallint, 
	maintain_interval integer, 
	date_maintain_last datetime, 
	fk_user_maintain_renew integer, 
	description text, 
	note_public text, 
	note_private text, 
	date_creation datetime, 
	date_validation datetime, 
	date_modification datetime, 
	tms timestamp, 
	fk_user_creat integer, 
	fk_user_modif integer, 
	fk_user_valid integer, 
	last_main_doc varchar(255), 
	import_key varchar(14), 
	model_pdf varchar(255), 
	status smallint
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
