# Contributing

Tickets
---
If you found a bug, have a feature request... please do not forget to include the phptal version you are using. In case of a bug report, please also add information/code on how to reproduce the error.

Bug fixes && new tests
---
Please create pull requests against the current version's base branch. At the time of writing, that would be branch 3.0.
Don't forget to include the ticket number your PR is refering to. 
Please try to include as few commits as possible (and feasible), ideally only one commit per pull request (use squash-merge for example).
Do not forget to update ```CHANGELOG.md```

New Features / Breaking changes
---
Handle the same way as bugfixes, but always use the master branch to create the pull request against.

General
---
For each pull request, please add appropriate unit tests for your changes. Please stick to PSR2 coding style guidelines.
We have added two handy shortcuts to run tests and code sniffer: ```composer tests && composer sniff```
