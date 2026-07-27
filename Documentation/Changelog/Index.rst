..  _changelog:

=========
Changelog
=========

Unreleased
==========

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
