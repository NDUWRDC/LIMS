-- ========================================================================
-- Copyright (C) 2020           David Bensel      <david.bensel@gmail.com>
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <https://www.gnu.org/licenses/>.
--
-- ========================================================================
CREATE TABLE llx_lims_units(
	rowid integer AUTO_INCREMENT PRIMARY KEY,
	code varchar(3),
	label varchar(50),
	short_label varchar(10),
	active tinyint DEFAULT 1 NOT NULL
)ENGINE=innodb;

ALTER TABLE llx_lims_units ADD UNIQUE uk_c_units_code(code);

INSERT INTO llx_lims_units (code, label, short_label, active) VALUES ('CFU', 'Colony Forming Units / 100ml', 'CFU/100ml', 1);
INSERT INTO llx_lims_units (code, label, short_label, active) VALUES ('GL', 'gramm / liter', 'g/l', 1);
INSERT INTO llx_lims_units (code, label, short_label, active) VALUES ('MGL', 'milli gramm / liter', 'mg/l', 1);
INSERT INTO llx_lims_units (code, label, short_label, active) VALUES ('PH', 'pH', 'pH', 1);
INSERT INTO llx_lims_units (code, label, short_label, active) VALUES ('TCU', 'True Color Units', 'TCU', 1);
INSERT INTO llx_lims_units (code, label, short_label, active) VALUES ('NTU', 'Naphthalometric Turbidity Unit', 'NTU', 1);
INSERT INTO llx_lims_units (code, label, short_label, active) VALUES ('CON', 'micro Siemens / centimeter', 'µS/cm', 1);