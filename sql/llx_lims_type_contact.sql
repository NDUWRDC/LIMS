-- ========================================================================
-- Copyright (C) 2021           David Bensel      <david.bensel@gmail.com>
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

-- rowid uses the value from the module ID
INSERT INTO llx_c_type_contact (rowid, element, source, code, libelle, active ) values (207150, 'Samples',  'internal', 'TECHNICIAN', 'Laboratory technician', 1);
INSERT INTO llx_c_type_contact (rowid, element, source, code, libelle, active ) values (207151, 'Samples',  'internal', 'OWNSAMPLER', 'Sampling technician', 1);
INSERT INTO llx_c_type_contact (rowid, element, source, code, libelle, active ) values (207152, 'Samples',  'external', 'CUSTOMERREPORT', 'Contact for Report', 1);