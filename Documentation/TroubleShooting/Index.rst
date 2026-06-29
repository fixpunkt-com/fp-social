..  include:: /Includes.rst.txt

..  _known-problems:

===============
Troubleshooting
===============

..  _known-problems-files-public:

Despite protection, all files are publicly accessible
=====================================================

Check whether a :file:`.htaccess` file exists in the root directory of the
storage and redirects requests to *fp_fileprotector*.

In general the extension updates those .htaccess file automatically. Try to open the storage settings in the fp_fileprotector Backend Modul and save the storage settings once, to update the .htaccess.

If nothing helps you can update the .htaccess on your own: A template can be found at :file:`Resources/Private/htaccess.txt`.

Also verify that the web server is configured to process :file:`.htaccess`
files (:bash:`AllowOverride All`).

..  _known-problems-groups-ignored:

User groups and/or users are being ignored
==========================================

Check whether the option **Must be logged in on the frontend** is enabled in
the access rule. Without this checkbox, group and user restrictions have no
effect.

..  _known-problems-or-conjunction:

A user has access even though they are not in any selected group
================================================================

The selection of groups and individual users is an **OR** conjunction. All
members of the selected groups **as well as** all individually selected users
can access the files.

..  _known-problems-lift-inherited:

How do I lift an inherited access rule?
=======================================

This depends on the settings of the storage. If the storage is set to disallow all access by default, this is not possible right now unfortunately. This is a known limitation — see :ref:`future-plans` for planned improvements.

If the storage is set to allow all access by default, just create an empty protection rule at the folder, where you want to lift all restrictions and leave
all conditions unset. Access will then be open for everyone for that folder and its subfolders.

If you want to be safe and deny all access that has not been explicitly granted, you can choose to set the storage to "allow access by default" and create a protection
rule on the root of the storage, which allows only backend users to access the storage. That given, all folders are restricted for frontend access,
but you can use empty protection rules to allow access for designated folders.

I have another problem
======================

Please feel free to open a GitHub Issue! :)