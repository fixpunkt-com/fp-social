..  _installation:

=============
Installation
=============

Follow the steps below to set up the ``fp_social`` extension correctly.

#.  Install the extension in your TYPO3 instance:

    *   *Classic:* Place the extension in your ``typo3_conf`` folder and
        install it manually in the ``Extension Manager``.
    *   *Composer:* Download the extension ``fixpunkt/fp-social`` from our
        Composer repository at ``https://composer.fixpunkt.com``. [#f1]_

#.  Include TypoScript in your template

    *   Add the static template ``fp_social`` to your template.
    *   Add the TypoScript constants
        ``plugin.tx_fpsocial.persistence.storagePid`` and
        ``module.tx_fpsocial.persistence.storagePid``.

#.  Create your :ref:`first access and social media account
    <access-and-accounts>`.
#.  Create a :ref:`Scheduler Task <scheduler-task>` so that the stored social
    media accounts are synchronized automatically.

..  [#f1] To get access to the Composer repository you need your individual
    access credentials.
