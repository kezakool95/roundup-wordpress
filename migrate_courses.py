import json

wa_courses = [
    {
        "id": 1,
        "name": "Joondalup Resort (Lake/Quarry)",
        "scratch": 75.0, "par": 72, "slope": 140,
        "location": "Connolly, WA",
        "type": "Public"
    },
    {
        "id": 2,
        "name": "Wembley Golf Course (Old)",
        "scratch": 69.0, "par": 72, "slope": 116,
        "location": "Wembley Downs, WA",
        "type": "Public"
    },
    {
        "id": 3,
        "name": "Collier Park Golf (Island/Pines)",
        "scratch": 71.5, "par": 72, "slope": 122,
        "location": "Como, WA",
        "type": "Public"
    },
    {
        "id": 4,
        "name": "The Vines Resort (Lakes)",
        "scratch": 72.0, "par": 72, "slope": 128,
        "location": "The Vines, WA",
        "type": "Public"
    },
    {
        "id": 5,
        "name": "Carramar Golf Course",
        "scratch": 71.0, "par": 72, "slope": 128,
        "location": "Carramar, WA",
        "type": "Public"
    }
]

# Simple script to generate WP-CLI commands
for course in wa_courses:
    print(f'post_id=$(docker exec -u root wordpress-migration-wordpress-1 wp post create --post_type=course --post_title="{course["name"]}" --post_status=publish --porcelain --allow-root)')
    print(f'docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id location "{course["location"]}" --allow-root')
    print(f'docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _location field_course_location --allow-root')
    print(f'docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id rating "{course["scratch"]}" --allow-root')
    print(f'docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _rating field_course_rating --allow-root')
    print(f'docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id par "{course["par"]}" --allow-root')
    print(f'docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _par field_course_par --allow-root')
    print(f'docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id holes "18" --allow-root')
    print(f'docker exec -u root wordpress-migration-wordpress-1 wp post meta update $post_id _holes field_field_course_holes --allow-root')
