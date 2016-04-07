CSV
===

CSV stands for comma-separated values.  It is a plain text file format,
supported by many applications.  It is often used for data exchange
operations, such as import and export, as well as configuration
definitions.

This document describes module configuration with CSV files.

Overview
--------

Our system consists of several modules.  Some of these modules have
very distinct properties and functionality and are hardcoded.  The
others can change significantly from installation to installation
and/or over time.  In order to faciliate such changes, we used CSV
files for module configurations.

There are three separate configurations that we support in CSV format:

1. Migrations
2. Views
3. Lists

The following sections will describe each in detail.

### Conventions

There is no strictly defined de juro format for the CSV files.  It's
more of a de facto standard, with some slight variations from
application to application.  This section defines our assumptions and
expectations, in order to minimize portability issues.

#### Files

* File names should be in English.
* File names should be in lowercase.
* Underscore character (_) should be used as a word separator.
* Files should have a .csv extension.
* File character encoding should be ASCII (plain text English).
* Files should use DOS/Windows line endings (\r\n).

#### Headers

* The first line of every CSV file should define column headers.
* Column headers are used only for increased human readability. They
	are ignored during import/export/configuration operations.
* Column headers should be in uppercase.
* Column headers should be in English.

#### Values

* Values (configuration parameters, field names, etc) should be in
	English.
* Values can be either quoted or unquoted.
* Quoted values should be properly escaped.

Simple values, such as field names and types, can be used without
quoting.  For example:

```csv
first_name,string,255
last_name,string,255
```

Complex values, such as those requiring punctuation, must be quoted
with double quote characters (").
For example:

```csv
person_name,"Mamchenkov, Leonid"
person_address,"Nicosia, Cyprus"
```

Complex values, which include double quotes in the value, have to be
escaped with the backslash character (\).  For example:

```csv
fighter,"Antonio \"Bigfoot\" Silva"
```

### Migrations

Migrations configuration defines the structure of the database tables
behind an application.  Each module has one and only one migration
configuration.

The CSV file configuring the migration is considered to be a 
representation of the current desired state of the table.

#### Columns

Migration file consists of the following columns:

* Field name - defines the name of the field in the database.
* Field type - defines the type of the field (see below).
* Field length - defines database field length (as per MySQL).
* Required - defines whether or not the field is a required one.
* Not Searchable - defines whether or not the field is searchable.

##### Field name

Field names should always be in lowercase, with underscore character
used as a word separator.  Field name is unique for the module. That is
you can have, for example, a field 'status' defined in the migration
files for different modules, but you cannot have the field 'status'
defined twice in the same module.

No field names are required, however, we strongly recommend that for
consistency and best practices reasons, all modules always contain the
following fields:

* id (of type uuid)
* created (of type datetime)
* modified (of type datetime)

The order of the field names in the CSV file is not important, however
we recommend that the 'id' field is always first, and the 'created' and
'modified' fields are always last.

##### Field type

Field type defines the type of the value that can be stored in the
field, as well how the user interface elements will be rendered when
displaying the value or creating an input form element.  For example,
for field type 'datetime' a date time picker will be automatically
provided.

###### Support field types

The following field types are supported:

* uuid - [Universally Unique
	Identifier](https://en.wikipedia.org/wiki/Universally_unique_identifier)
* string - for storing character values up to 255 characters in length.
* text - for storing character values larger than 255 characters in
	length.
* datetime - for storing date and time values
* date - for storing date values
* time - for storing time values
* boolean - for storing on/off, yes/no, and other binary values
* integer - for storing integeger values
* list:LISTNAME - for storing string values from a predefined list (see
	below)
* related:MODULE - for storing uuid record of a related module (see
	below)

###### Special field type : list

It is often that an application need to work with predefined lists of
values and labels.  Some of the examples are : list of countries, list
of currencies, list of statuses, etc.  We support this with a special
field type: list:LISTNAME.

LISTNAME should be replaced with the name of the CSV file, defining the
list values (see below).  For example, 'list:countries',
'list:currencies'.  The same list can be used in multiple modules,
which makes the values consistent.  If multiple modules define the same
field name, for example - type or status, and need to use different
list values, depending on the module, it is suggested to prefix the
list name with the module name.  For example: 'list:accounts_statuses',
and 'list:lead_statuses'.

###### Special field type: related

It is often that a record in one module is related to the record in
another module.  For example, when a Task is assigned to a User, they
are considered to be related.  The most common type of relationship in
databases in called
[one-to-many](https://en.wikipedia.org/wiki/One-to-many_(data_model)).

In one-to-many relationship, one side of the relationship can be
related to multiple records on other side of the relationship.  For
example, one User can be assigned many Tasks.  But the Task can only be
assigned to a single User.

In order to record this in the database, a Task module migration needs
to have a field, which will store the id of the User to who the task is
assigned.

In the migrations CSV file you can define this with the field type
'related:MODULE', where MODULE should be the name of the module to
which the relationship is needed.

The opposite direction of the relationship (in the example above, from
Users to Tasks) is handled automatically and you don't need to define
anything in the other module's migration CSV.

##### Field length

Field length defines the maximum size of the value that can be stored
in the database.  For string fields, this represent the number of
characters, for numeric fields, this represents the number of bytes,
etc.

Field length parameter is not required.  For those fields which require
a length parameter in the database, the following default values will
be assumed:

* integer - 11 bytes
* varchar - 255 characters
* text - the value of the LONG_TEXT used by the current database

Also, the following field length values will be **always** assumed:

* uuid - 36 bytes
* boolean - 1 byte (for storing 0 or 1)
* time - 8 bytes (for storing time as hh:mm:ss format using 24 hours)
* date - 10 bytes (for storing date as YYYY-MM-DD format)
* datetime - 19 bytes (for storing date and time as 'YYYY-MM-DD
	hh:mm:ss' format)

##### Required

Required column defines whether or not the field is required.  When set to
required, records with empty values for required fields will not be allowed
to be saved.  This rule is enforced on the database level.

##### Not Searchable

By default, the application assumes that all fields in the module are
searchable.  If certain fields have to be excluded from searching, then
this column can be used to do so.

### Views

Views configuration defines how the user screens will be constructed for
each module.  There are currently 4 screens per module:

* add - defines the screen for creating new records
* edit - defines the screen for updating existing records
* view - defines the screen for detailed view of a single record
* index - defines the screen for the listing of multiple records

Each view can be customized separately, but for consistency reasons it is
recommended that add, edit, and view screens are either the same, or very
similar.


#### Columns (add/edit/view)

Add, edit, and view screens are constructed based on the following
assumptions:

1. Record fields are organized into one or more  panels, such as General,
   Details, etc.
2. Record fields are displayed in two vertical columns.

Files for the add, edit, and view screen configurations consist of the 
following columns:

* Panel name - human-friendly name of the panel
* First column - field name that goes into the first (left) column
* Second column - field name that goes into the second (right) column

If no field is configured for the column, an empty space will be displayed
instead.

#### Columns (index)

Files for index screen configurations consist of a single column:

* Field name 

which defines which fields will be displayed in the listing of the records
in the current module, and their order as well.

### Lists

Lists configuration defines which lists are available in the system for the
lists in migrations configurations.

#### Columns

List file consists of the following columns:

* Value - the value to store in the database when item is selected
* Label - user-friendly label to show to the user
* Inactive - whether or not the list item is active

In order to avoid data inconsistency and unexpected results during record
updating, it is **strongly** recommended to not ever delete list items from
the CSV files, but to mark them Inactive.
