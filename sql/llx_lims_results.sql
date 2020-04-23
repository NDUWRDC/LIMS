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


CREATE TABLE llx_lims_results(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) DEFAULT '(PROV)' NOT NULL, 
	fk_samples integer NOT NULL, 
	fk_user integer NOT NULL, 
	fk_method integer NOT NULL, 
	result real NOT NULL, 
	start datetime NOT NULL, 
	end datetime NOT NULL, 
	abnormalities text, 
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14), 
	status smallint NOT NULL, 
	model_pdf varchar(255), 
	rang integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
