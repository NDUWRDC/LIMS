# CHANGELOG [LIMS](https://github.com/NDUWRDC/LIMS) FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Equipment: Add Equipment from existing Products or Services
- Equipment: Define category 'Equipment', 'Consumable', 'Facility', 'Sales Item'
- Equipment: Define one of options 'No calibration, or maintainance', 'maintainance', 'calibration'
- Equipment: Define maintainance/calibration intervall
- Equipment: Each Event is logged: added-validated-modified-invalidated(set to draft)-Renew Readiness-PDF created-PDF modified
- Equipment: List Equipment view
- Equipment: Prefix for object reference can be defined via Settings.
- Reports: Calibration and Maintenance Report (Doc template 'standard_equipmentlist') at Equipment-List
- ChangeLog added
### Changed
- Samples: Client sampling person is now one of the options empty-yes-no
### Removed
- Samples: At creation the option to create a contact for client sample taker 
### Fixed
- Samples: Change order of lines (results) wasn't working
- Limits: Change order of lines (results) wasn't working
- Samples: Cloning wasn't working
- Samples: Label at header wasn't ediable with pen-icon
- Methods: In list view next|previous wasn't working properly

## [0.1.0](https://github.com/NDUWRDC/LIMS/releases/tag/v0.1) - 2020-09-16
### Added
- Methods: Add Methods which have a link to an existing product or service
- Methods: Define 'unit' (e.g. mS/cm), Accuracy, Measurement Range, Resolution of reading
- Limits: Add limit sets, with each entry/line linked to one method
- Limits: Define Minimum and Maximum, where one value may be NULL
- Samples: Manage Samples, where each sample has linked to a customer
- Samples: Define sampling place and time, sampling person, volume, etc.
- Samples: Muliple tests, where each test is linked to one method; each test is linked to the responsible person
- Samples: Nonconformities
- Samples: Apply a limit set to the sample
- Results: List view of all results and their correlating samples
- Reports: User right 'View Reports'
- Reports: Test Report on the basis of ISO 17025 (Doc template 'lims_testreport') at Samples
- Settings: Prefixes for object references (Methods, Samples, Results, Limits)
- User rights: 3 basic rights for all objects => View / Create,Update / Validate,Delete