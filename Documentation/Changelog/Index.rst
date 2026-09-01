..  _changelog:

=========
Changelog
=========

Unreleased
==========

*   Accounts can now be filled in from the SocialServer: at the top of the
    :guilabel:`Setting` section the button :guilabel:`Configure account
    automatically` offers all channels that are connected to the selected
    credentials, and one click writes channel and label into the record. If the
    chosen channel belongs to another network, the record is saved and the
    network is switched along with it. See :ref:`access-and-accounts`.
*   Added support for Instagram accounts that are connected through the direct
    Instagram login instead of a Facebook page.
*   Added compatibility with TYPO3 v14. The extension now supports TYPO3 v12.4,
    v13.4 and v14.4.
*   For TYPO3 v13 and v14 the TypoScript can now be loaded via the site set
    :guilabel:`Social Wall` (``fixpunkt/fp-social``). The static template
    remains available for TYPO3 v12. See :ref:`installation`.
*   Removed usage of APIs that were deprecated or removed in TYPO3 v14.
*   The plugins now live in an own group :guilabel:`Social Wall` in the content
    element wizard and in the type selector. For TYPO3 v12, where the wizard is
    not generated from the TCA, the entries are provided via page TSconfig.
*   The plugin, palette and tab labels of the content elements are localized
    now, and both plugins have their own icon.
