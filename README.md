# FRP Funding Filter Module

## Features
- Dynamic AJAX filtering of funding opportunities
- Region and category taxonomy filters
- Drupal 9/10 compatible
- Block and route integration

## Installation
1. Copy to `/modules/custom/frp_funding_filter`
2. Enable module via Drush or admin UI

## Usage
1. Create vocabularies: "region" and "funding_category"
2. Add fields to "funding_opportunity" content type
3. Place block or visit `/funding-filter`

## Requirements
- Drupal 9+
- Taxonomy module