# Conswaggle Contribution Workflow

This document outlines the Git workflow and branch protection rules for contributing to the Roundup WordPress repository.

## Overview

The repository uses a protected `staging` branch to ensure code quality and maintain a stable integration environment. All contributions must go through a pull request (PR) workflow with code review.

## Branch Protection Rules

The `staging` branch has the following protections in place:

- **Require Pull Requests**: All code changes must be submitted via a pull request
- **Required Approvals**: 1 approval required before merging
- **Block Force Pushes**: Force pushes are prevented to maintain history integrity
- **Restrict Deletions**: The branch cannot be deleted

## Contribution Workflow

### Step 1: Set Up Your Local Repository

```bash
# Clone the repository
git clone https://github.com/kezakool95/roundup-wordpress.git
cd roundup-wordpress

# Add upstream (if working with a fork)
git remote add upstream https://github.com/kezakool95/roundup-wordpress.git
```

### Step 2: Create Your Feature Branch

Create a new branch from your personal branch (e.g., `conswaggle`):

```bash
# Switch to your branch
git checkout conswaggle

# Pull the latest updates from staging
git pull origin staging

# Create a new feature branch from conswaggle
git checkout -b feature/your-feature-name
```

**Branch Naming Convention**:
- `feature/` for new features
- `bugfix/` for bug fixes
- `docs/` for documentation updates
- `refactor/` for code refactoring

Example: `feature/add-membership-validation`

### Step 3: Make Your Changes

```bash
# Make your code changes
# ...

# Stage your changes
git add .

# Commit your changes with a descriptive message
git commit -m "Add descriptive commit message"

# Push to your branch
git push origin feature/your-feature-name
```

**Commit Message Guidelines**:
- Use clear, descriptive messages
- Start with a verb (Add, Fix, Update, Refactor, etc.)
- Reference issue numbers if applicable: `Fix #123`
- Example: `Fix: Correct PHP tags in stats page`

### Step 4: Create a Pull Request

1. Go to the [repository on GitHub](https://github.com/kezakool95/roundup-wordpress)
2. You should see a prompt to create a PR from your feature branch
3. Click **"Compare & pull request"**
4. Ensure the base branch is `staging` (not `main`)
5. Add a descriptive title and detailed description
6. Click **"Create pull request"**

**Pull Request Template**:
```
Title: Brief description of changes

Description:
- What problem does this solve?
- What changes were made?
- How to test?

Related Issues:
Closes #123
```

### Step 5: Code Review

1. Your PR will be reviewed by the repository maintainer
2. Address any requested changes by:
   ```bash
   # Make additional changes
   git add .
   git commit -m "Address review feedback"
   git push origin feature/your-feature-name
   ```
3. The PR will automatically update with new commits

### Step 6: Merge to Staging

Once approved:
1. The maintainer will merge your PR to `staging`
2. Your changes will be integrated with other code
3. Your feature branch can be deleted

```bash
# Update your local conswaggle branch
git checkout conswaggle
git pull origin staging

# Delete your local feature branch (optional)
git branch -d feature/your-feature-name
```

## Pulling From Staging

To get the latest code from the staging branch:

```bash
# Make sure you're on your working branch
git checkout conswaggle

# Pull the latest from staging
git pull origin staging
```

## Pushing to Staging

**You cannot push directly to staging.** You must:

1. Create a feature branch from your `conswaggle` branch
2. Make your changes
3. Push your feature branch to GitHub
4. Create a pull request to `staging`
5. Wait for code review and approval
6. The maintainer will merge your PR

## Common Git Commands

```bash
# View branches
git branch -a

# Switch branches
git checkout branch-name

# Create and switch to new branch
git checkout -b new-branch-name

# View commit history
git log --oneline

# Undo uncommitted changes
git checkout -- filename

# Revert a commit
git revert commit-hash

# Sync with remote
git fetch origin
git merge origin/staging
```

## Troubleshooting

### "Permission denied" when trying to push
You cannot push directly to the `staging` branch. Create a feature branch instead.

### Merge conflicts
If you have conflicts:
```bash
# Resolve conflicts in your editor
# Then:
git add .
git commit -m "Resolve merge conflicts"
git push origin feature/your-feature-name
```

### Pull request blocked
If your PR is blocked:
- Address any feedback from the code review
- Ensure all required checks pass
- Push additional commits to resolve issues

## Questions?

Reach out to the repository maintainer for clarification or assistance.
