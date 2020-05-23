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


CREATE TABLE llx_lims_samples(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) DEFAULT '(PROV)' NOT NULL, 
	fk_soc integer NOT NULL, 
	fk_propal integer, 
	fk_facture integer, 
	fk_socpeople integer, 
	fk_user integer, 
	fk_user_approval integer,
	label varchar(255) NOT NULL, 
	volume real NOT NULL, 
	qty integer NOT NULL, 
	date datetime NOT NULL, 
	place varchar(128) NOT NULL, 
	place_lon real, 
	place_lat real, 
	date_arrival datetime NOT NULL, 
	fk_project integer, 
	description text NOT NULL, 
	note_public text, 
	note_private text, 
    fk_limits integer NOT NULL,
	date_creation datetime NOT NULL, 
	tms timestamp, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	last_main_doc varchar(255), 
	import_key varchar(14), 
	model_pdf varchar(255), 
	status smallint NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
