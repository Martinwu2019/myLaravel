// ecosystem.config.cjs
module.exports = {
    apps: [
        {
            name: "queue-worker",
            script: "artisan",
            args: "queue:work --queue=default --tries=3 --timeout=120",
            interpreter: "php",
            instances: 15,
            exec_mode: "cluster",
            env: {
                APP_ENV: "production",
            },
        },
    ],
};
