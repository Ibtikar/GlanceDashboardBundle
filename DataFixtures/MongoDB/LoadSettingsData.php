<?php

namespace Ibtikar\GlanceDashboardBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ibtikar\GlanceDashboardBundle\Document\Settings;

/**
 */

class LoadSettingsData implements FixtureInterface {

    public function load(ObjectManager $manager) {

        /********************* Goody Stars Data ****************************/
        $starsBriefAr = new Settings();
        $starsBriefAr->setKey("stars-brief-ar");
        $starsBriefAr->setCategories(array("stars"));
        $starsBriefAr->setValue('“نجمات مطبخ قودي” هي منصة مصممة لعشاق الطبخ يتشاركون فيها ذات الشغف ليبدعوا، ويتبادلوا معارفهم وخبراتهم وسواء كنت تحبين إلهام الناس وإمتاعهم او تستمتعين بكونكِ مصدراً للتفاؤل والإبداع، فيسعدنا أن تنضمي إلينا لتصبحي إحدى نجمات مطبخ قودي.');
        $manager->persist($starsBriefAr);

        $starsBriefEn = new Settings();
        $starsBriefEn->setKey("stars-brief-en");
        $starsBriefAr->setCategories(array("stars"));
        $starsBriefEn->setValue('“Goody Kitchen Stars” is a platform designed for cooking enthusiasts, who share the same passion to innovate and share their knowledge and experiences. Whether you like to inspire people and entertain them, or you enjoy being a source of optimism and creativity, we are pleased that you’d become one of Goody Kitchen Stars.');
        $manager->persist($starsBriefEn);

        $starsBenefitsAr = new Settings();
        $starsBenefitsAr->setKey("stars-benefits-ar");
        $starsBriefAr->setCategories(array("stars"));
        $starsBenefitsAr->setValue('<ul><li><p align="RIGHT" style="margin-bottom: 0.14in" dir="RTL"><font face="Lohit Devanagari"><span lang="hi-IN"><font face="Tahoma"><font size="2">ستتاح لكِ الفرصة لتجربة المنتجات الجديدة قبل طرحها في الأسواق، كما ستكون لكِ الأولوية في معرفة كل جديد يخص مطبخ قودي</font></font></span></font><font face="Tahoma, serif"><font size="2">.</font></font><br type="_moz"></p></li><li><p align="RIGHT" style="margin-bottom: 0.14in" dir="RTL"><font face="Lohit Devanagari"><span lang="hi-IN"><font face="Tahoma"><font size="2">ستتاح لكِ الفرصة لتطوير وتحسين مهاراتك، وذلك بدعوتكِ لكثير من الأنشطة المتنوعة مثل ورش عمل تدريبية وغيرها</font></font></span></font><font face="Tahoma, serif"><font size="2">. </font></font><br type="_moz"></p></li><li><p align="RIGHT" style="margin-bottom: 0.14in" dir="RTL"><font face="Lohit Devanagari"><span lang="hi-IN"><font face="Tahoma"><font size="2">التعرف على سيدات مثلك يشاركنك نفس الإهتمامات ونفس الشغف </font></font></span></font><br type="_moz"></p></li><li><p align="RIGHT" style="margin-bottom: 0.14in" dir="RTL"><font face="Lohit Devanagari"><span lang="hi-IN"><font face="Tahoma"><font size="2">التعبير عن نفسك بأريحية وثقة، والحصول على التقدير الذي تستحقين</font></font></span></font><br type="_moz"></p></li><li><p align="RIGHT" style="margin-bottom: 0.14in" dir="RTL"><font face="Lohit Devanagari"><span lang="hi-IN"><font face="Tahoma"><font size="2">مشاهدة إنجازتك حقيقة ماثلة تعتزين وتفخرين بها</font></font></span></font></p></li></ul>');
        $manager->persist($starsBenefitsAr);

        $starsBenefitsEn = new Settings();
        $starsBenefitsEn->setKey("stars-benefits-en");
        $starsBriefAr->setCategories(array("stars"));
        $starsBenefitsEn->setValue('<ul><li><p style="margin-top: 0.02in; margin-bottom: 0.02in; line-height: 100%"><font face="Times New Roman, serif"><font size="3"><font face="Tahoma, serif"><font size="2">You’ll have the priority in knowing all the new updates concerning Goody Kitchen.</font></font></font></font></p></li><li><p style="margin-top: 0.02in; margin-bottom: 0.02in; line-height: 100%"><font face="Times New Roman, serif"><font size="3"><font face="Tahoma, serif"><font size="2">Developing and improving your skills and hobbies and learning new skills.</font></font></font></font></p></li><li><p style="margin-top: 0.02in; margin-bottom: 0.02in; line-height: 100%"><font face="Times New Roman, serif"><font size="3"><font face="Tahoma, serif"><font size="2">Getting to know women like you who are participating with the same interests and passion.</font></font></font></font></p></li><li><p style="margin-top: 0.02in; margin-bottom: 0.02in; line-height: 100%"><font face="Times New Roman, serif"><font size="3"><font face="Tahoma, serif"><font size="2">Your opinions will be appreciated and focused on in all our platforms and programs.</font></font></font></font><br type="_moz"></p></li><li><p style="margin-top: 0.02in; margin-bottom: 0.02in; line-height: 100%"><font face="Times New Roman, serif"><font size="3"><font face="Tahoma, serif"><font size="2">Watching your achievements become a reality, which will make you proud of it.</font></font></font></font></p></li></ul>');
        $manager->persist($starsBenefitsEn);

        $manager->flush();
    }

}
