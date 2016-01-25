# WP-CLI Unsplash Command

Download and import images from [Unsplash](https://unsplash.com) into your Media Library. Useful for generating demo data.

## Usage

Basic usage, which will import 10 images:

`wp media generate`

#### `count=<number>`

Import 100 images:

`wp media generate --count=100`

#### `media_date=<yyyy-mm-dd|random>`

Import images with a specific attachment date:

`wp media generate --media_date=2016-01-25`

Import images with random attachment dates:

`wp media generate --media_date=random`

#### `media_dimensions=<dimensions>`

Import images with specific dimensions:

`wp media generate --media_dimensions=1080x720`