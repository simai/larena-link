# Implementation Summary

`PublicLinkControlledDeliverySimulationPreview` in `larena/link` now owns final
report assembly and the local/testing dependency composition for the controlled
delivery simulation preview. It composes persistent lookup, guarded delivery
readiness, token fingerprinting and denied-case fixtures inside the package,
then builds response-envelope safety checks, negative delivery simulations,
access/audit trace checks, raw-token leak guard, file-delivery block and JSON
evidence writing.

The entry app `App\Larena\PublicLinkControlledDeliverySimulationFoundation`
compatibility wrapper is removed. Existing root controllers, providers,
console commands and tests now call the package preview API directly.

This batch preserves the existing schema
`larena.public_link_controlled_delivery_simulation_foundation.v1`, internal
route/controller/view behavior and public route metadata behavior that blocks
file content.
