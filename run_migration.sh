post_id=$(docker exec -u root wordpress-migration-wordpress-1 wp post create --post_type=course --post_title="Joondalup Resort (Lake/Quarry)" --post_status=publish --porcelain --allow-root)
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id location "Connolly, WA" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _location field_course_location --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id rating "75.0" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _rating field_course_rating --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id par "72" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _par field_course_par --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id holes "18" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _holes field_field_course_holes --allow-root
post_id=$(docker exec -u root wordpress-migration-wordpress-1 wp post create --post_type=course --post_title="Wembley Golf Course (Old)" --post_status=publish --porcelain --allow-root)
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id location "Wembley Downs, WA" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _location field_course_location --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id rating "69.0" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _rating field_course_rating --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id par "72" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _par field_course_par --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id holes "18" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _holes field_field_course_holes --allow-root
post_id=$(docker exec -u root wordpress-migration-wordpress-1 wp post create --post_type=course --post_title="Collier Park Golf (Island/Pines)" --post_status=publish --porcelain --allow-root)
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id location "Como, WA" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _location field_course_location --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id rating "71.5" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _rating field_course_rating --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id par "72" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _par field_course_par --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id holes "18" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _holes field_field_course_holes --allow-root
post_id=$(docker exec -u root wordpress-migration-wordpress-1 wp post create --post_type=course --post_title="The Vines Resort (Lakes)" --post_status=publish --porcelain --allow-root)
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id location "The Vines, WA" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _location field_course_location --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id rating "72.0" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _rating field_course_rating --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id par "72" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _par field_course_par --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id holes "18" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _holes field_field_course_holes --allow-root
post_id=$(docker exec -u root wordpress-migration-wordpress-1 wp post create --post_type=course --post_title="Carramar Golf Course" --post_status=publish --porcelain --allow-root)
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id location "Carramar, WA" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _location field_course_location --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id rating "71.0" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _rating field_course_rating --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id par "72" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _par field_course_par --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id holes "18" --allow-root
docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _holes field_field_course_holes --allow-root
