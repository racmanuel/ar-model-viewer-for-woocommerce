# AR Model Viewer API

Base URL: `https://example.com/wp-json/ar-model-viewer/v1`

## Public catalog

`GET /products`

Returns published, catalog-visible WooCommerce products whose effective parent configuration has a 3D model. The response is public and does not expose private viewer settings.

Query parameters:

- `page`: page number, starting at `1`.
- `per_page`: number of products per page, from `1` to `100`; default `20`.
- `search`: WooCommerce product search text.
- `category`: WooCommerce product category slug.

Each product contains `product_id`, `name`, `slug`, `model_url`, `poster_url`, `ios_src`, `has_ar`, `variation_count`, and `model_endpoint`. Use `model_endpoint?variation_id={id}` to resolve a specific variation with parent fallbacks.

The response has this shape:

```json
{
  "products": [],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 0,
    "pages": 0
  },
  "filters": {
    "search": "",
    "category": ""
  }
}
```

## Product model

`GET /products/{product_id}/model`

Returns the public viewer configuration for one product. Add `variation_id` for a variation belonging to that product. The endpoint returns `404` when the product is missing, disabled, or has no effective model.

## Analytics

`GET /analytics`

Returns anonymous daily aggregates. This endpoint requires an authenticated user with the `manage_options` capability. It never returns raw requests, visitor identifiers, IP addresses, or user data.

Query parameters:

- `from`: inclusive ISO date (`YYYY-MM-DD`), default 30 days before today.
- `to`: inclusive ISO date, default today.
- `product_id`: filter by product.
- `variation_id`: filter by variation.
- `event`: one of the event names accepted by `POST /events`.
- `mode`: filter by AR mode.
- `group_by`: `day`, `event`, `product`, `variation`, or `mode`; default `event`.
- `page`: page number, starting at `1`.
- `per_page`: number of grouped rows, from `1` to `200`; default `50`.

The response includes a filtered `summary`, the normalized date range, active filters, grouped rows, and pagination metadata. Historical rows created before variation tracking use `variation_id: 0`.

## Event ingestion

`POST /events`

Public endpoint used by the viewer. Analytics must be enabled in plugin settings before events are stored. Accepted fields are `event`, `product_id`, `variation_id`, `mode`, `error_code`, and `duration_bucket`. A request is limited to 30 accepted events per PHP request and does not retain visitor identity.

## Errors

REST validation errors use the normal WordPress REST error format with an `code`, `message`, and `data.status`. Private analytics requests without the required capability receive `401` or `403` according to the active WordPress authentication mechanism.
