# CHANGELOG [LIMS](https://github.com/NDUWRDC/LIMS) FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
__Dolibarr version 13.x required__
### Added
- Equipment: Add Equipment from existing Products or Services
- Equipment: Define category 'Equipment', 'Consumable', 'Facility', 'Sales Item'
- Equipment: Define one of options 'No calibration, or maintainance', 'maintainance', 'calibration'
- Equipment: Define maintainance/calibration intervall
- Equipment: Each Event is logged: added-validated-modified-invalidated(set to draft)-Renew Readiness-PDF created-PDF modified
- Equipment: List Equipment view
- Settings: Prefix for Equipment object references can be changed. Default='EQ'
- Results: List view has 'Units' added.
- Samples and Results: With every approval following the first 'BACK TO DRAFT' a revision counter is increased.
- Samples and Results: Read-only field 'last_modifications' shows what changed with the last modification (MODIFY pressed).
- Samples and Results: Each Event is logged: added-validated-modified-invalidated(set to draft)-PDF created 
- Samples: Selected Limit Set is now displayed with label
- Samples: Lines (results) show selected Product - Method with tooltip
- Samples: Add lines (results) shows Product-Method now with product reference and label.
- Samples: Date of validation registered automatically and printed on report instead of current date
- Reports: Calibration and Maintenance Report (Doc template 'standard_equipmentlist') at Equipment-List
- Dictionaries: LIMS Units for easy management of units  (pH, mg/l, ...)
- Dictionaries: LIMS Locations to provide options for the current location of a sample (customer, fridge, laboratory, fridge, ...)
- New sql files: llx_lims_units.sql, llx_lims_locations.sql
- ChangeLog added
### Changed
- __BREAKING:__ sql files changed, update from 0.1.0 to 0.2.0 not possible, __fresh install required__
- Samples: Client sampling person is now one of the options empty-yes-no
- Samples: MODIFY only possible when sample is not validated
- Samples: List view changed: 'Current location', 'Version', 'Approval date' added
- Methods: At creation (New Method) 'Status' is not visible
- Limits: At creation (New Limit Set) 'Status' is not visible
- Results: 'Abnormality' is relabeld to 'Nonconformity'
- Results: List view changed: 'Start', 'End', 'Unit' added
- Test Report: Statement of Conformity based on the information whether all results are within the respective measurement range of the method.
- Test Report: The revision version is appended to the file name, e.g. SA2101-0001rev2.pdf
### Removed
- Samples: At creation the option to create a contact for client sample taker 
- Samples: Proposal selection is now hidden
- tpl-files (objectline_create/title/view) since they are not used 
### Fixed
- Language keys for all labels
- Samples: Change order of lines (results) wasn't working
- Samples: Modify line values 'Test performed by' wasn't working
- Limits: Change order of lines wasn't working
- Limits: Check for multiple methods was faulty
- Samples: Cloning wasn't working
- Samples: Label at header wasn't editable with pen-icon
- Methods: In list view next|previous wasn't working properly
- Methods: Delete button link now with new token to avoid CSRF protection errors

## [0.1.0](https://github.com/NDUWRDC/LIMS/releases/tag/v0.1) - 2020-09-16
### Added
- Methods: Add Methods which have a link to an existing product or service
- Methods: Define 'unit' (e.g. mS/cm), Accuracy, Measurement Range, Resolution of reading
- Limits: Add limit sets, with each entry/line linked to one method
- Limits: Define Minimum and Maximum, where one value may be NULL
- Samples: Manage Samples, where each sample has linked to a customer
- Samples: Define sampling place and time, sampling person, volume, etc.
- Samples: Each sample can have multiple results (tests)
- Samples: Apply a limit set to the sample
- Samples: Can be generated from a validated invoice (module Invoices required) linking samplet to Third-Party and Invoice
- Results: List view of all results and their correlating samples
- Results: Linked to the responsible person
- Results: Linked to one method
- Results: Have a flag 'nonconformities'
- Reports: User right 'View Reports'
- Reports: Test Report on the basis of ISO 17025 (Doc template 'lims_testreport') at Samples
- Settings: Prefixes for object references (Methods, Samples, Results, Limits)
- User rights: 3 basic rights for all objects => View / Create,Update / Validate,Delete
- User rights: One additional right to view reports