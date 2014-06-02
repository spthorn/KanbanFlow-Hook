# KanbanFlow-Hook

This project contains the glue that's required in order to connect GitHub commits to KanbanFlow tasks. It's implemented in PHP.

## Requirements

1. KanbanFlow account configured with swimlanes. This means the Premium version (the free version doesn't support swimlanes).
2. Github repository that you want to track commits on. You'll need to modify the Settings for this repository, so you need to own it.
3. PHP web server to hold the glue file.

## Kanbanflow Configuration

Log into your KanbanFlow board. Click the Administration link at the top right. Click the Board Settings button for the board of your choice. 

1. Click the Numbering of tasks tab. Click the Activate numbering button to enable task numbering.
  1. Select Create new numbering and click Next.
  2. Type a prefix and start value. Pick a prefix that's not likely to appear in any of your commit messages, e.g. "KF". Click Next.
  3. Enter a name for this numbering scheme. Any name will do. Click Activate.
2. Click the API tab. If you haven't already, click the Add API token button to create a new Secret token. This will be used later.
3. Create at least one new task, which will pick up the task numbering. Note that the number will appear to the left of the task name on your board, e.g. "KF1: Write form validators".

## Github Configuration

Go to your repository and click the Settings link on the right. Click the Webhooks & Services menu option on the left. Click the Add webhook button. Here's what you need to set up:

1. Payload URL: The URL of your PHP file, e.g. "http://www.mydomain.com/github_hook.php"
2. Content type: application/json
3. Secret: Your KanbanFlow Secret token you created earlier.
4. Select which events you want to trigger the webhook. Typically, it'll be just the push event.
5. Click Active.
6. Click Create webhook.

## PHP Configuration

Copy the single PHP file to your PHP web server.

## Testing

You need to create a commit, and include the KanbanFlow task number, preceded by the pound sign, in the commit message, e.g. "Added date validator #KF1". Push the commit to your github repository.

Github will search through all your webhooks, and trigger each one that was configured to look for the push event, by sending an http request to the URL you configured.

Back at your github Webhooks & services page, click the pencil icon next to the webhook you created earlier. At the bottom of the page, you'll see an entry in the Recent Deliveries section. Click the ellipsis icon at the right to show the Request and Response tabs. The latter should show "Returned {"taskCommentId":"09cbbd1fa8fcbc0b485b71400f5030ca"}" or the like. 

Didn't it? Click the Redeliver button to re-trigger the http request and debug your code. Any HTTP or PHP errors will appear in the Response tab.

Once you get the expected response, verify that your KanbanFlow task contains a new comment.

## TODO

1. Modify the code to support non-swimlanes, i.e. github repository name = board name




