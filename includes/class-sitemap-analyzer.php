<?php
namespace WPCI\Includes;

class SitemapAnalyzer {
	private $sitemaps = [];
	private $urls = [];

	public function __construct() {
		// Nothing to init
	}

	public function discover_sitemaps() {
		$home_url = home_url( '/' );
		$potential_sitemaps = [
			$home_url . 'sitemap.xml',
			$home_url . 'sitemap_index.xml',
			$home_url . 'wp-sitemap.xml', // WordPress default
		];

		// Check robots.txt for Sitemap directive
		$robots_txt = wp_remote_get( $home_url . 'robots.txt' );
		if ( ! is_wp_error( $robots_txt ) && wp_remote_retrieve_response_code( $robots_txt ) === 200 ) {
			$body = wp_remote_retrieve_body( $robots_txt );
			if ( preg_match_all( '/Sitemap:\s*(.*)/i', $body, $matches ) ) {
				foreach ( $matches[1] as $sitemap ) {
					$potential_sitemaps[] = trim( $sitemap );
				}
			}
		}

		$this->sitemaps = array_unique( $potential_sitemaps );
		return $this->sitemaps;
	}

	public function get_all_urls() {
		if ( ! empty( $this->urls ) ) {
			return $this->urls;
		}

		$sitemaps = $this->discover_sitemaps();
		foreach ( $sitemaps as $sitemap ) {
			$this->parse_sitemap( $sitemap );
		}

		$this->urls = array_unique( $this->urls );
		return $this->urls;
	}

	private function parse_sitemap( $url ) {
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return;
		}

		$xml = wp_remote_retrieve_body( $response );
		$sitemap_obj = simplexml_load_string( $xml );

		if ( ! $sitemap_obj ) {
			return;
		}

		// Handle Sitemap Index
		if ( isset( $sitemap_obj->sitemap ) ) {
			foreach ( $sitemap_obj->sitemap as $sub_sitemap ) {
				$this->parse_sitemap( (string) $sub_sitemap->loc );
			}
		}

		// Handle URL Set
		if ( isset( $sitemap_obj->url ) ) {
			foreach ( $sitemap_obj->url as $url_entry ) {
				$this->urls[] = (string) $url_entry->loc;
			}
		}
	}

	public function get_unloved_pages( $days = 30 ) {
		global $wpdb;
		$table_name = Database::get_table_name();
		$sitemap_urls = $this->get_all_urls();

		if ( empty( $sitemap_urls ) ) {
			return [];
		}

		$crawled_urls = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT url FROM $table_name WHERE timestamp > %s",
			date( 'Y-m-d H:i:s', strtotime( "-$days days" ) )
		) );

		return array_diff( $sitemap_urls, $crawled_urls );
	}
}
