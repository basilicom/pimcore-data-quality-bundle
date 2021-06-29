# Data Quality Bundle for Pimcore

With this bundle the data quality of objects can be checked and displayed.

-------

## Installation
1. Install the bundle using ``composer require basilicom/pimcore-data-quality-bundle``
2. Execute ``bin/console pimcore:bundle:install DataQualityBundle``

## Configuration
1. Add a new object ``Data Quality Config`` in your object tree
2. Choose an object class from the first select box and hit ``save and publish``
3. Now you can configure areas and fields you want to check and show in the data quality overview
4. Add the new field type ``dataQuality`` from the layout components to the chosen object class
5. (Optional) Add the field type ``number`` with name ``DataQualityPercent`` to the chosen object class if you want to have total percent indicator

-------

**Author:** Conrad Guelzow (Basilicom GmbH)  
**License:** GPL v3
