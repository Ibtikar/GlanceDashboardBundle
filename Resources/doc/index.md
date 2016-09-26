Installation steps
==================

1.In your project composer.json file "extra" section add the following information

    "extra": {
        "installer-paths": {
            "src/Ibtikar/GlanceDashboardBundle/": ["Ibtikar/GlanceDashboardBundle"]
        }
    }

2.Require the package using composer by running

    composer require Ibtikar/GlanceDashboardBundle

3.Add to your appkernel the next line
            new Ibtikar\GlanceDashboardBundle\IbtikarGlanceDashboardBundle(),


4.Add this route to your routing file

   ibtikar_glance_dashboard:
        resource: "@IbtikarGlanceDashboardBundle/Resources/config/routing.yml"
        prefix:   /backend


5.Add the next line to your .gitignore file

    /src/Ibtikar/GlanceDashboardBundle

