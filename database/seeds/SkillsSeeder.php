<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class SkillsSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $data = [
            // Frontend Technologies
            ['name' => 'HTML', 'category' => 'Frontend'],
            ['name' => 'CSS', 'category' => 'Frontend'],
            ['name' => 'JavaScript', 'category' => 'Frontend'],
            ['name' => 'TypeScript', 'category' => 'Frontend'],
            ['name' => 'React', 'category' => 'Frontend'],
            ['name' => 'Vue.js', 'category' => 'Frontend'],
            ['name' => 'Angular', 'category' => 'Frontend'],
            ['name' => 'Svelte', 'category' => 'Frontend'],
            ['name' => 'Next.js', 'category' => 'Frontend'],
            ['name' => 'Nuxt.js', 'category' => 'Frontend'],
            ['name' => 'Tailwind CSS', 'category' => 'Frontend'],
            ['name' => 'Bootstrap', 'category' => 'Frontend'],
            ['name' => 'Sass/SCSS', 'category' => 'Frontend'],

            // Backend Technologies
            ['name' => 'PHP', 'category' => 'Backend'],
            ['name' => 'Python', 'category' => 'Backend'],
            ['name' => 'Java', 'category' => 'Backend'],
            ['name' => 'Node.js', 'category' => 'Backend'],
            ['name' => 'C#', 'category' => 'Backend'],
            ['name' => 'Go', 'category' => 'Backend'],
            ['name' => 'Ruby', 'category' => 'Backend'],
            ['name' => 'Rust', 'category' => 'Backend'],
            ['name' => 'Laravel', 'category' => 'Backend'],
            ['name' => 'Symfony', 'category' => 'Backend'],
            ['name' => 'Django', 'category' => 'Backend'],
            ['name' => 'Flask', 'category' => 'Backend'],
            ['name' => 'FastAPI', 'category' => 'Backend'],
            ['name' => 'Spring Boot', 'category' => 'Backend'],
            ['name' => 'Express.js', 'category' => 'Backend'],
            ['name' => 'NestJS', 'category' => 'Backend'],
            ['name' => '.NET Core', 'category' => 'Backend'],
            ['name' => 'Ruby on Rails', 'category' => 'Backend'],

            // Databases
            ['name' => 'MySQL', 'category' => 'Database'],
            ['name' => 'PostgreSQL', 'category' => 'Database'],
            ['name' => 'MongoDB', 'category' => 'Database'],
            ['name' => 'Redis', 'category' => 'Database'],
            ['name' => 'SQLite', 'category' => 'Database'],
            ['name' => 'Oracle', 'category' => 'Database'],
            ['name' => 'Microsoft SQL Server', 'category' => 'Database'],
            ['name' => 'MariaDB', 'category' => 'Database'],
            ['name' => 'Cassandra', 'category' => 'Database'],
            ['name' => 'DynamoDB', 'category' => 'Database'],
            ['name' => 'Elasticsearch', 'category' => 'Database'],

            // DevOps & Cloud
            ['name' => 'Docker', 'category' => 'DevOps'],
            ['name' => 'Kubernetes', 'category' => 'DevOps'],
            ['name' => 'AWS', 'category' => 'Cloud'],
            ['name' => 'Azure', 'category' => 'Cloud'],
            ['name' => 'Google Cloud Platform', 'category' => 'Cloud'],
            ['name' => 'Git', 'category' => 'DevOps'],
            ['name' => 'GitHub Actions', 'category' => 'DevOps'],
            ['name' => 'GitLab CI/CD', 'category' => 'DevOps'],
            ['name' => 'Jenkins', 'category' => 'DevOps'],
            ['name' => 'Terraform', 'category' => 'DevOps'],
            ['name' => 'Ansible', 'category' => 'DevOps'],
            ['name' => 'Linux', 'category' => 'DevOps'],
            ['name' => 'Nginx', 'category' => 'DevOps'],
            ['name' => 'Apache', 'category' => 'DevOps'],

            // Mobile Development
            ['name' => 'React Native', 'category' => 'Mobile'],
            ['name' => 'Flutter', 'category' => 'Mobile'],
            ['name' => 'Swift', 'category' => 'Mobile'],
            ['name' => 'Kotlin', 'category' => 'Mobile'],
            ['name' => 'Android', 'category' => 'Mobile'],
            ['name' => 'iOS', 'category' => 'Mobile'],
            ['name' => 'Ionic', 'category' => 'Mobile'],

            // Data Science & AI
            ['name' => 'Machine Learning', 'category' => 'AI/ML'],
            ['name' => 'Deep Learning', 'category' => 'AI/ML'],
            ['name' => 'TensorFlow', 'category' => 'AI/ML'],
            ['name' => 'PyTorch', 'category' => 'AI/ML'],
            ['name' => 'Scikit-learn', 'category' => 'AI/ML'],
            ['name' => 'Pandas', 'category' => 'Data Science'],
            ['name' => 'NumPy', 'category' => 'Data Science'],
            ['name' => 'Data Analysis', 'category' => 'Data Science'],
            ['name' => 'Data Visualization', 'category' => 'Data Science'],
            ['name' => 'Power BI', 'category' => 'Data Science'],
            ['name' => 'Tableau', 'category' => 'Data Science'],

            // Testing
            ['name' => 'Unit Testing', 'category' => 'Testing'],
            ['name' => 'Integration Testing', 'category' => 'Testing'],
            ['name' => 'Jest', 'category' => 'Testing'],
            ['name' => 'PHPUnit', 'category' => 'Testing'],
            ['name' => 'Pytest', 'category' => 'Testing'],
            ['name' => 'Selenium', 'category' => 'Testing'],
            ['name' => 'Cypress', 'category' => 'Testing'],

            // Other Skills
            ['name' => 'GraphQL', 'category' => 'API'],
            ['name' => 'REST API', 'category' => 'API'],
            ['name' => 'gRPC', 'category' => 'API'],
            ['name' => 'WebSockets', 'category' => 'API'],
            ['name' => 'Microservices', 'category' => 'Architecture'],
            ['name' => 'Domain-Driven Design', 'category' => 'Architecture'],
            ['name' => 'Event-Driven Architecture', 'category' => 'Architecture'],
            ['name' => 'RabbitMQ', 'category' => 'Message Queue'],
            ['name' => 'Apache Kafka', 'category' => 'Message Queue'],
            ['name' => 'Agile', 'category' => 'Methodology'],
            ['name' => 'Scrum', 'category' => 'Methodology'],
        ];

        $skills = $this->table('skills');
        $skills->insert($data)->saveData();
    }
}
