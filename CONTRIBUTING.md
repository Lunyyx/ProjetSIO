# Guide de contribution - Fit&Fun

## Comment contribuer au projet

### Prérequis
- Git installé
- DDEV installé et configuré
- Connaissance de base en PHP, HTML, CSS

### Processus de contribution

1. **Fork du projet**
   - Forker le repository sur GitHub
   - Cloner votre fork localement

2. **Créer une branche**
   ```bash
   git checkout -b feature/nom-de-la-fonctionnalite
   ```

3. **Développer**
   - Faire vos modifications
   - Tester localement avec DDEV
   - Suivre les conventions de code

4. **Commit**
   ```bash
   git add .
   git commit -m "Description claire de la modification"
   ```

5. **Push et Pull Request**
   ```bash
   git push origin feature/nom-de-la-fonctionnalite
   ```
   - Créer une Pull Request sur GitHub
   - Décrire clairement les changements

### Conventions de code

#### PHP
- Utiliser les standards PSR-12
- Commenter les fonctions complexes
- Nommer les variables de manière explicite
- Utiliser les requêtes préparées pour la BDD

#### HTML/CSS
- Indentation de 4 espaces
- Classes CSS en kebab-case
- HTML5 sémantique

#### Git
- Commits atomiques et descriptifs
- Messages en français
- Format : "Type: Description"
  - Exemples : "Feature: Ajout du système de paiement"
  - "Fix: Correction de l'inscription aux activités"
  - "Docs: Mise à jour du README"

### Structure des commits

```
Type: Description courte (50 caractères max)

Description détaillée si nécessaire (optionnel).
Expliquer le pourquoi, pas le comment.

Fixes #123 (si correction d'issue)
```

Types de commits :
- `Feature` : Nouvelle fonctionnalité
- `Fix` : Correction de bug
- `Docs` : Documentation
- `Style` : Formatage, espaces, etc.
- `Refactor` : Refactorisation
- `Test` : Ajout de tests
- `Chore` : Tâches de maintenance

### Tests

Avant de soumettre une Pull Request :
- [ ] Tester toutes les nouvelles fonctionnalités
- [ ] Vérifier qu'aucune régression n'est introduite
- [ ] Tester sur différents navigateurs (Chrome, Firefox, Safari)
- [ ] Tester le responsive design
- [ ] Vérifier qu'il n'y a pas d'erreur PHP

### Checklist avant Pull Request

- [ ] Le code compile sans erreur
- [ ] Les fonctionnalités sont testées
- [ ] La documentation est à jour
- [ ] Les commits sont propres et descriptifs
- [ ] Le code respecte les conventions du projet
- [ ] Les fichiers temporaires sont exclus (.gitignore)

### Questions et support

Pour toute question, ouvrir une issue sur GitHub avec le label `question`.

---

Merci de contribuer à Fit&Fun ! 🎉
