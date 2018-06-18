<?php

namespace Truonglv\MultiFileMirror;

use XF\Db\Schema\Create;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\AddOn\StepRunnerUninstallTrait;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1()
    {
        $this->upgrade1000370Step1();
        $this->upgrade1000470Step1();
        $this->upgrade1000670Step1();
    }

    public function uninstallStep1()
    {
        $this->schemaManager()->dropTable('tl_mfm_link');

        $this->query('DELETE FROM xf_job WHERE execute_class = ?', \Truonglv\MultiFileMirror\Job\Uploader::class);
    }

    public function upgrade1000370Step1()
    {
        try {
            $this->query('ALTER TABLE xf_attachment_data DROP COLUMN tl_mfm_link');
        } catch (\XF\Db\Exception $e) {
        }

        $sm = $this->schemaManager();
        $sm->createTable('tl_mfm_link', function (Create $table) {
            $table->addColumn('attachment_id', 'int')->unsigned();
            $table->addColumn('link', 'varchar', 150)->setDefault('');
            $table->addColumn('created_date', 'int')->unsigned()->setDefault(0);
            $table->addColumn('token', 'varchar', 32)->setDefault('');

            $table->addPrimaryKey('attachment_id');
            $table->addUniqueKey('attachment_id');
        });
    }

    public function upgrade1000470Step1()
    {
        try {
            $this->query("ALTER TABLE tl_mfm_link ADD COLUMN view_count INT UNSIGNED NOT NULL DEFAULT '0'");
        } catch (\XF\Db\Exception $e) {
        }
    }

    public function upgrade1000670Step1()
    {
        $this->query('DELETE FROM xf_job WHERE execute_class = ?', \Truonglv\MultiFileMirror\Job\Uploader::class);

        try {
            $this->query("ALTER TABLE tl_mfm_link ADD COLUMN uploaded_date INT UNSIGNED NOT NULL DEFAULT '0'");
        } catch (\XF\Db\Exception $e) {
        }

        try {
            $this->query("ALTER TABLE tl_mfm_link ADD COLUMN is_attach_removed TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
        } catch (\XF\Db\Exception $e) {
        }

        $this->app->jobManager()
                ->enqueueLater(
                    'tl_mfm_link',
                    \XF::$time + 120,
                    \Truonglv\MultiFileMirror\Job\Uploader::class
                );
    }
}
