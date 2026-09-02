..  _changelog:

=========
Changelog
=========

Unreleased
==========

*   The social server now limits how often its API may be called -- requests to
    the same social media profile and the overall number of requests are each
    capped per API user. Synchronization no longer treats a reached limit as a
    failure: short blocks are waited out and the request is retried, longer ones
    postpone the account to the next run. The new extension setting
    :guilabel:`rateLimitWaitThreshold` decides where that boundary is, see
    :ref:`rate-limit`.
*   Accounts carry a new read-out field :guilabel:`Synchronization postponed
    until`. As long as it lies in the future, the scheduler task skips the
    account. Clear it to try again right away.
*   The deep synchronization in the backend module now pauses and continues on
    its own when a limit is reached instead of stopping with an error.
*   Requests to the social server now use a connect timeout of 10 and a read
    timeout of 30 seconds. Previously a hanging server could block a
    synchronization run indefinitely.
*   Requires ``fixpunkt/fp-social-bridge`` 1.5 or newer.
*   An answer of the social server that cannot be used is reported with a
    message that names the cause: a mismatching protocol version and an unknown
    response type each state what does not fit and what helps, instead of the
    technical message of ``fp_social_bridge``. See :ref:`unusable-answer`.

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
