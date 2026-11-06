# Spam Blocker Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 5.0.3 - 2026-11-06
### Fixed
- Formie 4 compatibility: Formie 4 removed `Submissions::EVENT_BEFORE_SPAM_CHECK`, which stopped Craft loading with Formie 4 installed. Spam Blocker now hooks into Formie 4's `screen.evaluateSpam` workflow task, and still supports Formie 2/3.
- Formie Repeater, Group and multi-option field values weren't being checked against patterns.
- A non-string field value stopped the remaining fields from being checked.

## 5.0.2 - 2025-10-01
### Fixed
- Issue with Formie

## 5.0.1 - 2025-09-29
### Fixed
- Issue with Formie

## 4.0.3 - 2024-07-15
### Fixed
- Issue with Formie

## 4.0.2 - 2023-09-18
### Fixed
- Update errors
### Updated
- Icon

## 4.0.1 - 2023-09-18
### Fixed
- PHP error
### Updated
- Craft 3 references to Craft 4

## 4.0.0 - 2023-09-18
### Updated
- Made plugin Craft 4 compatible

## 0.0.1 - 2021-06-15
### Added
- Initial release
