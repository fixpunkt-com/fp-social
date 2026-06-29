# TYPO3 Extension: fp-fileprotector

The extension **fp-fileprotector** allows you to restrict access to file storages in TYPO3 and define granular access rules for individual files and folders.

## Table of Contents
1. [How it works](#how-it-works)
2. [Protecting a storage](#protecting-a-storage)
3. [Access rules for folders](#access-rules-for-folders)
    - [Inheritance](#inheritance)
    - [Overview & status colors](#overview--status-colors)
    - [Creating rules](#creating-rules)
4. [Troubleshooting](#troubleshooting)

---

## How it works
With *fp-fileprotector* you can make access to files conditional:
* **Frontend status:** Is a user logged in?
  * Check for specific user groups.
  * Check for specific individual users.
* **Backend status:** Is a user logged in to the backend?

---

## Protecting a storage

To use the features, you must first activate a storage in the backend module **File Protection** (under the main module "Files").

![alt_text][storage_list]

Click the **Edit button** to configure the following options:

* **Protected file storage:** Enables protection in general. Without this checkbox, the storage is publicly accessible.
* **Deny access when no release rule exists:**
    * **Disabled (whitelist mode):** Accessible by default, only marked folders are restricted.
    * **Enabled (blacklist mode):** Restricted by default, access must be explicitly granted per folder.

![alt_text][storage_edit]

---

## Access rules for folders

Every folder within a protected storage can have individual rules.

### Inheritance
* **Propagation:** Rules are automatically inherited by all subfolders.
* **Override:** If a subfolder has its own rules, they do not supplement the parent rules — they replace them completely.

### Overview & status colors
In the list view of the module (click the **eye icon**) you can see the status of all folders:

![alt_text][storage_show]
![alt_text][folder_list]

* **Green:** Access allowed for everyone.
* **Orange:** Access is restricted by a rule.
* **Red:** No one has access (e.g. when the storage denies access by default and no rule exists).

**Icons:**
* 🔒 **Orange lock:** A specific access rule is defined.
* 🔓 **Green open lock:** A rule is defined but contains no restrictions (access is open).
* **(inherited):** The rule was inherited from a parent folder.

### Creating rules
1. Select a folder in the page tree or click its name in the list.
2. Click **Create access protection** or **Edit access protection**.
3. Choose the desired criteria (groups, users, or backend login).

![alt_text][folder_show]
![alt_text][protection]

> **Note on logic:** The selection of groups and users is an **OR** conjunction. Both members of the selected groups and the individually selected users will have access.

---

## Troubleshooting

### Despite protection, all files are publicly accessible
Check whether a `.htaccess` file exists in the root directory of the storage that redirects requests to *fp_fileprotector*.
* A template can be found at: `Resources/Private/htacces.txt`.
* Make sure the web server is allowed to process `.htaccess` files (`AllowOverride All`).

### User groups are being ignored
Check whether the option **"Must be logged in on the frontend"** is enabled in the access rule. Without this checkbox, group restrictions have no effect.

### How do I lift an inherited lock?
Simply create a new access rule for the affected subfolder without selecting any conditions. This will open up access for that branch again.

---

[storage_show]: /Documentation/Images/storage_show.jpg "List view of all storages."
[folder_list]: /Documentation/Images/folder_list.png "List of all folders and access rules of a storage."
[folder_show]: /Documentation/Images/folder_show.png "Shows the current status of a single folder."
[protection]: /Documentation/Images/protection.png "Settings to protect the folder."
[storage_list]: /Documentation/Images/storage_list.png "List view of all storages."
[storage_edit]: /Documentation/Images/storage_edit.png "Storage settings."
