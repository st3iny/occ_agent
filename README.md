<!--
  - SPDX-FileCopyrightText: 2025 Richard Steinmetz
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# Occ Agent

This agent assists you in managing your Nextcloud instance without you having to know about each occ command.
It works by using a LLM to convert your text prompts to occ commands which will then b executed locally.

## Installation

1. Clone this repository into your apps directory: `cd /path/to/nextcloud/apps && git clone https://github.com/st3iny/occ_agent.git`
2. Install dependencies: `cd occ_agent && composer install`
3. Enable the app: `cd ../.. && php occ app:enable occ_agent`

## Usage

1. Export an OpenAI api key: `export OPEN_AI_KEY=sk-proj-yourkeyhere'
2. Run `php occ agent` to get started with an interactive prompt.

## Example

```
$ occ agent
You > Please delete the user 8c08fdeb-e5ea-429c-b861-a2667bbabd01
Agent > The user with ID `8c08fdeb-e5ea-429c-b861-a2667bbabd01` has been successfully deleted.

You > Some files of admin are not showing up
Agent > The scan for the admin's files has been completed. Here are the results:

- Folders scanned: 7
- Files scanned: 1
- New files found: 0
- Files updated: 0
- Files removed: 0
- Errors encountered: 0

All files should now be accounted for. If files are still missing from the view, please check other configurations or permissions related to the admin account.

You > Are there group folders and if so how many files do they contain?
Agent > Yes, there are group folders in the Nextcloud instance. Here are the details for the configured group folders:

1. **Folder Id: 8, Name: team**
   - Contains: 2 files

2. **Folder Id: 9, Name: team2**
   - Contains: 2 files

So, in total, there are 4 files across the two group folders.
```
