# Fitness Planning Manager

Un plugin WordPress pour gérer et afficher les plannings de clubs de fitness.

## Fonctionnalités

- Création et gestion de plannings pour les clubs de fitness
- Affichage des plannings avec filtres par ville et type
- Recherche de plannings par nom de club
- Aperçu des plannings PDF directement sur le site
- Téléchargement des plannings au format PDF

## Shortcodes

### Afficher un planning spécifique

```
[fitness_planning id="123"]
```

### Afficher une liste de plannings avec filtres

```
[fitness_planning_list]
```

Options disponibles :
- `city` : Filtrer par ville (slug)
- `type` : Filtrer par type de planning (slug)
- `limit` : Nombre de plannings à afficher (défaut: 12)
- `orderby` : Tri des plannings (défaut: date)
- `order` : Ordre de tri (ASC ou DESC, défaut: DESC)

Exemple :
```
[fitness_planning_list city="casablanca" type="ramadan" limit="6" orderby="title" order="ASC"]
```

## Design

Le plugin propose un design moderne et responsive avec :
- Une interface de filtrage intuitive
- Des cartes de planning attrayantes
- Un modal d'aperçu PDF avec navigation entre les pages
- Une adaptation complète aux appareils mobiles
