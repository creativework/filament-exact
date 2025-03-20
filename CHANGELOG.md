# Changelog

All notable changes to `filament-exact` will be documented in this file.

## 1.0.0 - 12-03-2025
- initial release

## 1.0.1 - 13-03-2025
- Added support for Webhooks
- Improved authorization and added CronLock

## 1.0.2 - 19-03-2025
- Changed parameter of ExactQueueJob from Connection to ExactService
- Added ExactService::refresh() method so you can refresh the token manually

## 1.0.3 - 20-03-2025
- Added Unit tests

## 1.0.4 - 20-03-2025
- Fixed bug where users get an error when registering an existing webhook. This should not give an error.
