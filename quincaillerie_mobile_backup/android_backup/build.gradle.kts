// android/build.gradle.kts

plugins {
    id("com.android.application") version "8.7.2" apply false
    id("org.jetbrains.kotlin.android") version "1.9.25" apply false
}

// Répertoire de build personnalisé (optionnel mais propre)
val newBuildDir = rootProject.layout.buildDirectory.dir("../../build").get()
rootProject.buildDir = newBuildDir.asFile

subprojects {
    val subBuildDir = newBuildDir.dir(project.name)
    project.buildDir = subBuildDir.asFile
}

// Forcer compileSdk / minSdk pour tous les sous-projets
subprojects {
    afterEvaluate {
        val androidExt = extensions.findByName("android")
        if (androidExt is com.android.build.gradle.BaseExtension) {
            if (androidExt.compileSdkVersion == null) {
                androidExt.compileSdkVersion(34)
                println("⚙️ compileSdk forcé à 34 pour ${project.name}")
            }
            if (androidExt.defaultConfig.minSdkVersion == null) {
                androidExt.defaultConfig.minSdkVersion(21)
            }
        }
    }
}

tasks.register<Delete>("clean") {
    delete(rootProject.buildDir)
}
