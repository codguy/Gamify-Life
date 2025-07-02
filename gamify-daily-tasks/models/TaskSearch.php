<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Task;

/**
 * TaskSearch represents the model behind the search form of `app\models\Task`.
 */
class TaskSearch extends Task
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['title', 'description', 'due_date', 'status', 'created_at', 'updated_at', 'completed_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param int|null $currentUserId The ID of the current user to filter tasks for.
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $currentUserId = null, $formName = null)
    {
        $query = Task::find();

        if ($currentUserId !== null) {
            $query->where(['user_id' => $currentUserId]);
        }
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            // 'user_id' is handled by the main 'where' clause if $currentUserId is provided.
            // Only add $this->user_id to filter if $currentUserId is null (e.g. for an admin view).
            'user_id' => ($currentUserId === null) ? $this->user_id : null,
            'due_date' => $this->due_date,
            // Timestamps are integers; exact match search might not be user-friendly.
            // For created_at/updated_at, range search or date part search is usually better.
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            'completed_at' => $this->completed_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'status', $this->status]);

        // Remove the user_id filter if it's null to avoid `user_id = NULL` which is not intended.
        if ($currentUserId !== null && isset($query->filterWhere['user_id']) && $query->filterWhere['user_id'] === null) {
            unset($query->filterWhere['user_id']);
        }


        return $dataProvider;
    }
}
