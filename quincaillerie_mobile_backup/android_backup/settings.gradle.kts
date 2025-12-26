// android/settings.gradle.kts
pluginManagement {
    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }

    // ⚙️ Version alignée avec Flutter 3.35.1 (AGP 8.9.1)
    plugins {
        id("com.android.application") version "8.9.1" apply false
        id("org.jetbrains.kotlin.android") version "1.9.25" apply false
    }
}

dependencyResolutionManagement {
    repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS)
    repositories {
        google()
        mavenCentral()
    }
}

// Nom du projet racine (important pour Gradle)
rootProject.name = "quincaillerie_mobile"

// Inclusion du module app principal
include(":app")
