..  _installation:

=============
Installation
=============

..  _requirements:

Requirements
============

*   TYPO3 v12.4, v13.4 or v14.4
*   PHP 8.1 or higher (TYPO3 v14 requires PHP 8.3 or higher)

Setup
=====

Follow the steps below to set up the ``fp_social`` extension correctly.

#.  Install the extension in your TYPO3 instance:

    *   *Composer (recommended):* Require the extension via Composer:

        ..  code-block:: bash

            composer require fixpunkt/fp-social

    *   *Classic:* Install the extension ``fp_social`` from the
        `TYPO3 Extension Repository (TER)
        <https://extensions.typo3.org/extension/fp_social>`__ via the
        :guilabel:`Extensions` backend module.

#.  Load the TypoScript configuration:

    *   *TYPO3 v13 and v14 (recommended):* Assign the site set
        :guilabel:`Social Wall` (``fixpunkt/fp-social``) to your site. In the
        backend open :guilabel:`Site Management > Sites`, edit your site and add
        the set on the :guilabel:`Sets` tab, or declare it as a dependency in
        your site configuration:

        ..  code-block:: yaml
            :caption: config/sites/<my-site>/config.yaml

            dependencies:
              - fixpunkt/fp-social

    *   *TYPO3 v12 (classic):* Add the static template :guilabel:`Social Wall`
        (``fp_social``) to your TypoScript template record.

#.  Set the TypoScript constants
    ``plugin.tx_fpsocial.persistence.storagePid`` and
    ``module.tx_fpsocial.persistence.storagePid``.

#.  Create your :ref:`first access and social media account
    <access-and-accounts>`.
#.  Create a :ref:`Scheduler Task <scheduler-task>` so that the stored social
    media accounts are synchronized automatically.
