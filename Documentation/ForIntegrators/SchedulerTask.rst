..  _scheduler-task:

==============
Scheduler Task
==============

To keep the configured accounts up to date, you can set up
`Scheduler Tasks <https://docs.typo3.org/c/typo3/cms-scheduler/main/en-us/>`__.

Available Tasks
===============

The following tasks are available to you:

*   ``fp_social:synchronize``: Synchronizes the created accounts if they are
    selected for automatic synchronization.

    *   ``amount`` (optional): Number of social networks to synchronize. A value
        of ``0`` means no limit.

*   ``fp_social:download``: Downloads all post images that have not yet been
    downloaded. Otherwise this happens when the respective post is displayed for
    the first time.

    *   ``amount`` (optional): Number of images to download. If no value is
        given, up to ``40`` images are downloaded.

*   ``fp_social:remove``: Deletes old posts that are older than the specified
    number of days and are not currently in use. Images that are no longer
    referenced (including the associated files) and hashtags are also removed in
    the process.

    *   ``days`` (required): Number of days after which posts are deleted.

Setting up a Task
=================

#.  Switch to the **Scheduler** module.
#.  Click the button to add a new task.
#.  Fill in the fields as follows:

    *   **Class:** Execute console commands
    *   **Frequency:** We recommend ``*/15 * * * *`` here. This means that the
        task is executed every 15 minutes.
    *   **CommandController Command:** Select one of the tasks listed above here.

#.  Save the task and check it by running it manually once.

..  warning::
    Make sure that you call the Scheduler. Please also note that the task can
    only run as often as the Scheduler is called.

    If the Scheduler is only called every 30 minutes, the tasks will likewise
    only run every 30 minutes, regardless of their setting under *Frequency*.

For more information about the Scheduler, see the
`official documentation <https://docs.typo3.org/c/typo3/cms-scheduler/main/en-us/>`__.
