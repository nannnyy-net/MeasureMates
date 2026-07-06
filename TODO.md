# TODO - Cooking conversion fix

## Step 1
Update `ConverterController` to return/store the correct numeric value: **units needed** (source units required to make the target amount), not the old `convert()` result.

## Step 2
Expand supported units in `MeasureMate/config/conversions.php` to include **pint, quart, gallon** with accurate US mL constants.

## Step 3
Improve/verify pluralization + phrasing in `ConversionService::buildConversionPhrase()` to be correct for singular/plural across supported units.

## Step 4
Update and extend `MeasureMate/tests/Unit/ConversionServiceTest.php`:
- Align assertions with the required meaning.
- Add example tests (cup->tbsp, cup->tsp, liter->ml, tbsp->tsp, etc.)
- Add a sweep to ensure calculations are not reversed.

## Step 5
Run PHPUnit to verify all tests pass.

