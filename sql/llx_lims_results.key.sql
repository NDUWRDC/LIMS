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


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_lims_results ADD INDEX idx_lims_results_rowid (rowid);
ALTER TABLE llx_lims_results ADD INDEX idx_lims_results_ref (ref);
ALTER TABLE llx_lims_results ADD INDEX idx_lims_results_fk_samples (fk_samples);
ALTER TABLE llx_lims_results ADD INDEX idx_lims_results_fk_user (fk_user);
ALTER TABLE llx_lims_results ADD INDEX idx_lims_results_fk_method (fk_method);
ALTER TABLE llx_lims_results ADD INDEX idx_lims_results_fk_user_creat (fk_user_creat);
ALTER TABLE llx_lims_results ADD INDEX idx_lims_results_fk_user_modif (fk_user_modif);
ALTER TABLE llx_lims_results ADD CONSTRAINT idx_lims_results_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_lims_results ADD UNIQUE INDEX uk_lims_results_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_lims_results ADD CONSTRAINT llx_lims_results_fk_field FOREIGN KEY (fk_field) REFERENCES llx_lims_myotherobject(rowid);