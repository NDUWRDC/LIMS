# LABORATORY INFORMATION MANAGEMENT SYSTEM FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## Features v0.2.0

__Manage your samples and generate test reports following ISO 17025:2017__ 
* Manage Equipment, where each equipment has
  * A link to an existing product (Module PRODUCTS required) or service (Module SERVICES required)
  * A category such as 'Equipment', 'Consumable', 'Facility', 'Sales Item'
  * Options 'No calibration, or maintainance', 'maintainance', 'calibration'
  * A maintainance/calibration intervall
  * Events  logged: added-validated-modified-invalidated(set to draft)-Renew Readiness-PDF created-PDF modified
* Calibration and Maintenance Report (Doc template 'standard_equipmentlist') listing all equipment available at ```LIMS - Equipment - List Equipment```
* Manage Test Methods, where each method has
  * A link to an equipment
  * A unit, e.g. µS/cm (units are managed via a dictionary)
  * Accuracy
  * Measurement Range
  * Resolution of reading
* Manage Limit Sets, with each entry/line
  * Linked to one method
  * Minimum and Maximum, where one value may be empty (NULL)
* Manage Samples, where each sample has
  * A link to a customer (Module THIRD PARTIES required)
  * A link to a customer invoice (Module INVOICES required)
  * Details on the sample, such as sampling place and time, sampling person, volume, etc.
  * Muliple tests, where each test is linked to one method; each test is linked to the responsible person
  * A limit set applied to the sample results
* Manage Results, where each result
  * Belongs only to one sample
  * Has a test method, technician, start/end time, result
  * Has information about nonconformity
* Technical records with history
  * Ammendments can be tracked to previous versions
  * Modifications of results and sample information are stored to Agenda (module EVENTS/AGENDA required) 
* Test Report (Doc template 'lims_testreport') available at ```LIMS - Samples - Samples List -> Select Sample -> Card Tab```
  * Sample details
  * Results with indication if result is out of the limit range or out of the method's measurement range
  * Names of person(s) responsible and manager who authorized the sample.
* Samples can be created from a validated invoice (module INVOICES required): Details on customer and products which are part of Equipment will be added to the sample. Products/Services listed on the invoice which are part of LIMS Equipment-Sales Items are added as lines (tests) to the sample.
* Settings: Prefix for object references can be changed. 
  * Samples, default='SA' => SA-2104-0001 (YYMM-nnnn)
  * Results, default='RE' => RE-2104-0001
  * Methods, default='ME' => ME-2104-0001
  * Equipment, default='EQ' => EQ-2104-0001
  * Limits, default='LI' => LI-2104-0001
* Dictionary for management of units
  * Use a predefined set of units for methods
  * Add / change / delete units easily
* Dictionary for management of locations
  * Keep track of the current location of the sample 
  * Add / change / delete locations easily
<!--
![Screenshot lims](img/screenshot_lims.png?raw=true "LIMS"){imgmd}
-->

Other modules are available on [Dolistore.com](https://www.dolistore.com>).

## Translations

Translations can be defined manually by editing files into directories *langs*. Currently available language files: English.

<!--
This module contains also a sample configuration for Transifex, under the hidden directory [.tx](.tx), so it is possible to manage translation using this service.

For more informations, see the [translator's documentation](https://wiki.dolibarr.org/index.php/Translator_documentation).

There is a [Transifex project](https://transifex.com/projects/p/dolibarr-module-template) for this module.
-->


## Installation

### From the ZIP file and GUI interface

* Download a release package from https://github.com/NDUWRDC/LIMS/releases
* Log into Dolibarr as admininistrator and browse to ```Home - Setup - Modules/Applications```
  * Select tab ```Deploy/install external app/module```
  * Select the zip-file (release package) and click SEND

Note: If this screen tell you there is no custom directory, check your setup is correct:

- In your Dolibarr installation directory, edit the ```htdocs/conf/conf.php``` file and check that following lines are not commented:

    ```php
    //$dolibarr_main_url_root_alt ...
    //$dolibarr_main_document_root_alt ...
    ```

- Uncomment them if necessary (delete the leading ```//```) and assign a sensible value according to your Dolibarr installation

    For example :

    - UNIX:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';
        ```

    - Windows:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';
        ```

### From a GIT repository

- Clone the repository in ```$dolibarr_main_document_root_alt/lims```

```sh
cd ....../custom
git clone https://github.com/NDUWRDC/LIMS.git lims 
```

### <a name="final_steps"></a>Final steps

From your browser:

  - Log into Dolibarr as administrator
  - Go to ```Home - Setup - Modules/Applications```
  - You should now be able to find and enable the module LIMS

## Licenses

### Main code

GPLv3 or (at your option) any later version. See file [COPYING](COPYING) for more information.

### Documentation

All texts and readmes are licensed under GFDL.
